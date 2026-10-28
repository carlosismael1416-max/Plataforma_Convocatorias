<?php

namespace App\Services;

use App\Models\Propuesta;
use App\Models\PropuestaRevision;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Envío de propuestas a revisión y dictamen del directivo.
 *
 * - Docentes y directivos envían sus propias propuestas (pasan a ENVIADA).
 * - Cualquier directivo activo las revisa, incluidas las suyas: aprobar,
 *   devolver con observaciones (vuelve a EDITANDO) o rechazar.
 * - Cada envío y dictamen queda en propuesta_revisiones (historial con las
 *   observaciones) y el autor recibe el dictamen como notificación si su
 *   preferencia «nuevas_propuestas» está activa (el historial queda siempre).
 */
class RevisionPropuesta
{
    public const EDITABLES = ['BORRADOR', 'GENERADA', 'EDITANDO', 'LISTA'];

    public const DECISIONES = [
        'APROBAR' => 'APROBADA',
        'DEVOLVER' => 'EDITANDO',
        'RECHAZAR' => 'RECHAZADA',
    ];

    /**
     * Lo que le falta a la propuesta para poder enviarse (vacío si está lista).
     *
     * @return list<string>
     */
    public static function faltantes(Propuesta $propuesta): array
    {
        // Mismas secciones principales que la vista previa; las cotizaciones
        // no bloquean porque no aplican a todos los proyectos.
        $conTexto = fn ($consulta) => $consulta->whereRaw("BTRIM(descripcion) <> ''");

        $secciones = [
            // En infraestructura (FAM) la metodología y el impacto van por
            // proyecto, no en los datos generales.
            'Datos generales' => collect($propuesta->esInfraestructura()
                ? [$propuesta->titulo, $propuesta->resumen, $propuesta->justificacion]
                : [$propuesta->titulo, $propuesta->resumen, $propuesta->justificacion, $propuesta->metodologia, $propuesta->impacto_esperado]
            )->every(fn ($valor): bool => \App\Support\TextoEnriquecido::texto($valor) !== ''),

            'Objetivos' => $conTexto($propuesta->objetivos()->where('tipo', 'GENERAL'))->exists()
                && $conTexto($propuesta->objetivos()->where('tipo', 'ESPECIFICO'))->exists(),

            'Requisitos' => ! $propuesta->requisitos()->where('cumplido', false)->exists(),

            'Presupuesto' => $propuesta->itemsPresupuesto()->exists(),

            'Cronograma' => $propuesta->actividadesCronograma()->exists(),

            'Entregables' => $propuesta->entregables()->exists(),
        ];

        if ($propuesta->esInfraestructura()) {
            $proyectos = $propuesta->proyectosInfraestructura()->with(['itemsPresupuesto', 'laminas'])->get();

            $secciones['Diagnóstico de infraestructura'] = \App\Support\TextoEnriquecido::texto($propuesta->diagnostico) !== '';
            $secciones['Proyectos de infraestructura (objetivo, justificación, descripción, presupuesto y láminas)'] =
                $proyectos->isNotEmpty() && $proyectos->every(fn ($proyecto): bool => $proyecto->completo());
        }

        return array_keys(array_filter($secciones, fn (bool $completa): bool => ! $completa));
    }

    public static function enviar(Propuesta $propuesta, User $autor): void
    {
        if (
            ! $autor->estado
            || ! ($autor->tieneRol('DOCENTE') || $autor->tieneRol('DIRECTIVO'))
            || $propuesta->user_id !== $autor->getKey()
        ) {
            throw new AuthorizationException('Solo el autor puede enviar su propuesta.');
        }

        $faltantes = self::faltantes($propuesta);

        if ($faltantes !== []) {
            throw ValidationException::withMessages([
                'propuesta' => 'Completa antes de enviar: ' . implode(', ', $faltantes) . '.',
            ]);
        }

        DB::transaction(function () use ($propuesta, $autor): void {
            $actual = Propuesta::query()
                ->whereKey($propuesta->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($actual->estado, self::EDITABLES, true)) {
                throw ValidationException::withMessages([
                    'propuesta' => 'La propuesta ya fue enviada o dictaminada.',
                ]);
            }

            PropuestaRevision::create([
                'propuesta_id' => $actual->id,
                'user_id' => $autor->getKey(),
                'accion' => 'ENVIAR',
                'estado_anterior' => $actual->estado,
                'estado_nuevo' => 'ENVIADA',
            ]);

            $actual->estado = 'ENVIADA';
            $actual->fecha_envio = now();
            RegistroActividad::sinRegistroAutomatico(fn () => $actual->save());
            RegistroActividad::registrar('PROPUESTA_ENVIADA', 'Envió a revisión la propuesta «' . mb_strimwidth((string) $actual->titulo, 0, 160, '…') . '»', $actual, $autor->getKey());

            self::actualizarSeguimiento($actual, 'ENVIADA', 'Propuesta enviada a revisión');

            $directivos = User::query()
                ->where('estado', true)
                ->whereHas('role', fn ($q) => $q->where('nombre', 'DIRECTIVO'))
                ->whereKeyNot($autor->getKey())
                ->pluck('id');

            foreach ($directivos as $directivoId) {
                Avisos::notificar($directivoId, 'nuevas_propuestas', [
                    'propuesta_id' => $actual->id,
                    'convocatoria_id' => $actual->convocatoria_id,
                    'titulo' => 'Propuesta por revisar',
                    'mensaje' => trim($autor->name . ' ' . ($autor->apellidos ?? ''))
                        . ' envió «' . $actual->titulo . '».',
                    'tipo' => 'PROPUESTA_ENVIADA',
                ]);
            }
        });

        $propuesta->refresh();
    }

    public static function dictaminar(
        Propuesta $propuesta,
        User $revisor,
        string $decision,
        ?string $observaciones = null
    ): void {
        if (! $revisor->estado || ! $revisor->tieneRol('DIRECTIVO')) {
            throw new AuthorizationException('Solo un directivo puede revisar propuestas.');
        }

        if (! array_key_exists($decision, self::DECISIONES)) {
            throw ValidationException::withMessages([
                'decision' => 'La decisión no es válida.',
            ]);
        }

        $observaciones = trim((string) $observaciones);

        if ($decision !== 'APROBAR' && $observaciones === '') {
            throw ValidationException::withMessages([
                'observaciones' => 'Escribe las observaciones para el autor.',
            ]);
        }

        DB::transaction(function () use ($propuesta, $revisor, $decision, $observaciones): void {
            // Bloqueo: dos directivos no pueden dictaminar a la vez.
            $actual = Propuesta::query()
                ->whereKey($propuesta->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($actual->estado !== 'ENVIADA') {
                throw ValidationException::withMessages([
                    'decision' => 'La propuesta ya no está pendiente de revisión.',
                ]);
            }

            PropuestaRevision::create([
                'propuesta_id' => $actual->id,
                'user_id' => $revisor->getKey(),
                'accion' => $decision,
                'estado_anterior' => $actual->estado,
                'estado_nuevo' => self::DECISIONES[$decision],
                'observaciones' => $observaciones !== '' ? $observaciones : null,
            ]);

            $actual->estado = self::DECISIONES[$decision];
            RegistroActividad::sinRegistroAutomatico(fn () => $actual->save());
            RegistroActividad::registrar('DICTAMEN', [
                'APROBAR' => 'Aprobó',
                'DEVOLVER' => 'Devolvió con observaciones',
                'RECHAZAR' => 'Rechazó',
            ][$decision] . ' la propuesta «' . mb_strimwidth((string) $actual->titulo, 0, 160, '…') . '»', $actual, $revisor->getKey());

            [$seguimiento, $titulo, $tipo] = match ($decision) {
                'APROBAR' => ['ACEPTADA', 'Propuesta aprobada', 'PROPUESTA_APROBADA'],
                'DEVOLVER' => ['PREPARANDO_PROPUESTA', 'Propuesta devuelta con observaciones', 'PROPUESTA_DEVUELTA'],
                'RECHAZAR' => ['RECHAZADA', 'Propuesta rechazada', 'PROPUESTA_RECHAZADA'],
            };

            self::actualizarSeguimiento($actual, $seguimiento, $titulo);

            $revisorNombre = trim($revisor->name . ' ' . ($revisor->apellidos ?? ''));
            $mensaje = '«' . $actual->titulo . '» — revisó ' . $revisorNombre . '.';

            if ($observaciones !== '') {
                $mensaje .= "\n\nObservaciones: " . $observaciones;
            }

            Avisos::notificar($actual->user_id, 'nuevas_propuestas', [
                'propuesta_id' => $actual->id,
                'convocatoria_id' => $actual->convocatoria_id,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
            ]);
        });

        $propuesta->refresh();
    }

    private static function actualizarSeguimiento(Propuesta $propuesta, string $estado, string $accion): void
    {
        $seguimiento = UsuarioConvocatoria::query()
            ->where('user_id', $propuesta->user_id)
            ->where('convocatoria_id', $propuesta->convocatoria_id)
            ->first();

        if (! $seguimiento) {
            return;
        }

        $seguimiento->estado_seguimiento = $estado;
        $seguimiento->ultima_accion = $accion;
        $seguimiento->fecha_ultima_accion = now();
        $seguimiento->save();
    }
}
