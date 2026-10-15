<?php

namespace App\Services;

use App\Models\Convocatoria;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Decisión del directivo sobre una convocatoria PENDIENTE_REVISION:
 *
 * - APROBAR: pasa a PUBLICADA (no si la fecha de cierre ya pasó).
 * - SOLICITAR_CORRECCIONES: pasa a REQUIERE_CORRECCIONES (el docente que la
 *   subió la corrige y la reenvía).
 * - RECHAZAR: pasa a DESCARTADA (requiere la migración 2026_10_15_120000).
 *
 * La decisión se registra en convocatoria_revisiones_administrativas en la
 * misma transacción y se avisa a quien subió la convocatoria.
 */
class DecisionDirectivoConvocatoria
{
    public const ESTADOS = [
        'APROBAR' => 'PUBLICADA',
        'SOLICITAR_CORRECCIONES' => 'REQUIERE_CORRECCIONES',
        'RECHAZAR' => 'DESCARTADA',
    ];

    private const MIGRACION_RECHAZO = '2026_10_15_120000_allow_rechazar_in_historial_administrativo';

    public static function rechazoDisponible(): bool
    {
        return DB::table('migrations')
            ->where('migration', self::MIGRACION_RECHAZO)
            ->exists();
    }

    public static function decidir(
        Convocatoria $convocatoria,
        User $directivo,
        string $accion,
        ?string $observaciones = null
    ): void {
        if (
            ! $directivo->estado
            || ! $directivo->tieneRol('DIRECTIVO')
            || ! $directivo->tienePermiso('aprobar_convocatorias')
        ) {
            throw new AuthorizationException('No tienes permiso para revisar convocatorias.');
        }

        if (! array_key_exists($accion, self::ESTADOS)) {
            throw ValidationException::withMessages(['accion' => 'La decisión no es válida.']);
        }

        if ($accion === 'RECHAZAR' && ! self::rechazoDisponible()) {
            throw ValidationException::withMessages([
                'accion' => 'El rechazo aún no está disponible: falta aplicar la migración de la base.',
            ]);
        }

        $observaciones = trim((string) $observaciones);

        if ($accion !== 'APROBAR' && mb_strlen($observaciones) < 5) {
            throw ValidationException::withMessages([
                'observaciones' => 'Escribe las observaciones (mínimo 5 caracteres) para quien subió la convocatoria.',
            ]);
        }

        $estadoNuevo = self::ESTADOS[$accion];

        DB::transaction(function () use ($convocatoria, $directivo, $accion, $observaciones, $estadoNuevo): void {
            // Bloqueo: dos directivos no pueden decidir a la vez.
            $actual = Convocatoria::query()
                ->whereKey($convocatoria->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($actual->estado !== 'PENDIENTE_REVISION') {
                throw ValidationException::withMessages([
                    'estado' => 'La convocatoria ya no está pendiente de revisión. Actualiza la página.',
                ]);
            }

            if (
                $accion === 'APROBAR'
                && $actual->fecha_cierre !== null
                && $actual->fecha_cierre->lt(today())
            ) {
                throw ValidationException::withMessages([
                    'fecha_cierre' => 'No se puede publicar una convocatoria cuya fecha de cierre ya pasó.',
                ]);
            }

            // Solo cambia el estado; no se tocan la URL, sus hashes ni los datos extraídos.
            DB::table('convocatorias')
                ->where('id', $actual->id)
                ->update(['estado' => $estadoNuevo, 'updated_at' => now()]);

            DB::table('convocatoria_revisiones_administrativas')->insert([
                'convocatoria_id' => $actual->id,
                'revisor_id' => $directivo->id,
                'accion' => $accion,
                'estado_anterior' => 'PENDIENTE_REVISION',
                'estado_nuevo' => $estadoNuevo,
                'observaciones' => $observaciones !== '' ? $observaciones : null,
                'fecha_decision' => now(),
            ]);

            $autorId = $actual->subido_por_user_id;

            if ($autorId && (int) $autorId !== (int) $directivo->id) {
                $mensaje = '«' . $actual->titulo . '»';

                if ($observaciones !== '') {
                    $mensaje .= "\n\nObservaciones: " . $observaciones;
                }

                Notificacion::create([
                    'user_id' => $autorId,
                    'convocatoria_id' => $actual->id,
                    'titulo' => [
                        'APROBAR' => 'Tu convocatoria fue publicada',
                        'SOLICITAR_CORRECCIONES' => 'Tu convocatoria requiere correcciones',
                        'RECHAZAR' => 'Tu convocatoria fue rechazada',
                    ][$accion],
                    'mensaje' => $mensaje,
                    'tipo' => 'CONVOCATORIA_DICTAMEN',
                    'leida' => false,
                ]);
            }
        }, 3);
    }
}
