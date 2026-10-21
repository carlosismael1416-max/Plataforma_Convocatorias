<?php

namespace App\Services;

use App\Models\Convocatoria;
use App\Models\ConvocatoriaRevisionAdministrativa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Revisión manual del administrador para las convocatorias que el validador
 * no publicó (PENDIENTE_REVISION) o que se descartaron (DESCARTADA).
 *
 * - Publicar: queda visible para docentes y directivos aunque el validador
 *   no la haya aprobado (decisión humana, con observación).
 * - Descartar: estado DESCARTADA; el motor ya no la revalida ni la publica.
 * - Devolver: de DESCARTADA a PENDIENTE_REVISION (el motor vuelve a revisarla).
 *
 * Publicar (APROBAR) y descartar (RECHAZAR) quedan en
 * convocatoria_revisiones_administrativas; todo queda además en el
 * historial de actividades.
 */
class RevisionConvocatoria
{
    public const REVISABLES = ['PENDIENTE_REVISION', 'DESCARTADA'];

    public static function puedeRevisar(?User $usuario): bool
    {
        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('ADMINISTRADOR')
            && $usuario->tienePermiso('aprobar_convocatorias');
    }

    /** Lo que impide publicarla tal como está (vacío si se puede). */
    public static function impedimentos(Convocatoria $convocatoria, ?string $tipo = null): array
    {
        $tipo ??= $convocatoria->tipo;
        $faltan = [];

        if (trim((string) $convocatoria->titulo) === '') {
            $faltan[] = 'No tiene título.';
        }

        if (! isset(Convocatoria::TIPOS[$tipo ?? ''])) {
            $faltan[] = 'Elige qué tipo de convocatoria es (financiamiento, beca, premio…).';
        }

        if ($convocatoria->fecha_cierre && $convocatoria->fecha_cierre->lt(today())) {
            $faltan[] = 'Ya venció (cerró el ' . $convocatoria->fecha_cierre->format('d/m/Y') . '). Corrige la fecha si se amplió.';
        }

        return $faltan;
    }

    public static function publicar(Convocatoria $convocatoria, User $admin, string $tipo, string $observaciones): void
    {
        self::autorizar($admin, $convocatoria);

        if ($faltan = self::impedimentos($convocatoria, $tipo)) {
            throw ValidationException::withMessages(['decision' => implode(' ', $faltan)]);
        }

        self::exigirObservacion($observaciones);

        self::cambiar($convocatoria, $admin, 'APROBAR', 'PUBLICADA', $observaciones, ['tipo' => $tipo]);

        RegistroActividad::registrar('CONVOCATORIA', 'Publicó tras revisión manual «' . self::titulo($convocatoria) . '»', $convocatoria, $admin->id);
    }

    public static function descartar(Convocatoria $convocatoria, User $admin, string $observaciones): void
    {
        self::autorizar($admin, $convocatoria);

        if ($convocatoria->estado !== 'PENDIENTE_REVISION') {
            throw ValidationException::withMessages(['decision' => 'Solo se descartan convocatorias no publicadas.']);
        }

        self::exigirObservacion($observaciones);

        self::cambiar($convocatoria, $admin, 'RECHAZAR', 'DESCARTADA', $observaciones);

        RegistroActividad::registrar('CONVOCATORIA', 'Descartó «' . self::titulo($convocatoria) . '»', $convocatoria, $admin->id);
    }

    public static function devolver(Convocatoria $convocatoria, User $admin, string $observaciones = ''): void
    {
        self::autorizar($admin, $convocatoria);

        if ($convocatoria->estado !== 'DESCARTADA') {
            throw ValidationException::withMessages(['decision' => 'Solo se devuelven convocatorias descartadas.']);
        }

        // La tabla de revisiones no admite esta transición (su CHECK solo
        // contempla aprobar y rechazar); queda en el historial de actividades.
        RegistroActividad::sinRegistroAutomatico(fn () => $convocatoria->forceFill(['estado' => 'PENDIENTE_REVISION'])->save());

        RegistroActividad::registrar('CONVOCATORIA', 'Devolvió a «no publicadas» «' . self::titulo($convocatoria) . '»', $convocatoria, $admin->id);
    }

    private static function autorizar(User $admin, Convocatoria $convocatoria): void
    {
        if (! self::puedeRevisar($admin)) {
            throw new AuthorizationException('No tienes permiso para revisar convocatorias.');
        }

        if (! in_array($convocatoria->estado, self::REVISABLES, true)) {
            throw ValidationException::withMessages(['decision' => 'Esta convocatoria ya no está pendiente de revisión.']);
        }
    }

    private static function exigirObservacion(string $observaciones): void
    {
        if (mb_strlen(trim($observaciones)) < 10) {
            throw ValidationException::withMessages(['observaciones' => 'Escribe en una frase por qué tomas esta decisión (al menos 10 caracteres).']);
        }
    }

    /** @param  'APROBAR'|'RECHAZAR'  $accion  valores que admite el CHECK de la tabla */
    private static function cambiar(Convocatoria $convocatoria, User $admin, string $accion, string $estadoNuevo, ?string $observaciones, array $extra = []): void
    {
        DB::transaction(function () use ($convocatoria, $admin, $accion, $estadoNuevo, $observaciones, $extra): void {
            $anterior = $convocatoria->estado;

            RegistroActividad::sinRegistroAutomatico(fn () => $convocatoria->forceFill(['estado' => $estadoNuevo] + $extra)->save());

            $revision = new ConvocatoriaRevisionAdministrativa;
            $revision->forceFill([
                'convocatoria_id' => $convocatoria->id,
                'revisor_id' => $admin->id,
                'accion' => $accion,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $estadoNuevo,
                'observaciones' => $observaciones ? trim($observaciones) : null,
                'fecha_decision' => now(),
            ])->save();
        });
    }

    private static function titulo(Convocatoria $convocatoria): string
    {
        return mb_strimwidth((string) $convocatoria->titulo, 0, 160, '…');
    }
}
