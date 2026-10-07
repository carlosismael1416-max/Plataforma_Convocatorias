<?php

namespace App\Services;

use App\Models\Convocatoria;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevisionAdministrativaConvocatoria
{
    public static function registrar(
        Convocatoria $convocatoria,
        User $revisor,
        string $accion,
        ?string $observaciones = null
    ): void {
        // Comprobar el permiso en el servidor,
        // independientemente de los botones de Filament.
        if (
            ! $revisor->estado
            || ! $revisor->tieneRol('ADMINISTRADOR')
            || ! $revisor->tienePermiso('aprobar_convocatorias')
        ) {
            throw new AuthorizationException(
                'No tienes permiso para revisar convocatorias.'
            );
        }

        $estados = [
            'APROBAR' => 'PUBLICADA',
            'SOLICITAR_CORRECCIONES' => 'REQUIERE_CORRECCIONES',
            'REENVIAR_REVISION' => 'PENDIENTE_REVISION',
        ];

        if (! array_key_exists($accion, $estados)) {
            throw ValidationException::withMessages([
                'accion' => 'La decisión administrativa no es válida.',
            ]);
        }

        $observaciones = trim((string) $observaciones);

        if (
            in_array(
                $accion,
                ['SOLICITAR_CORRECCIONES', 'REENVIAR_REVISION'],
                true
            )
            && $observaciones === ''
        ) {
            throw ValidationException::withMessages([
                'observaciones' =>
                    'Debes describir las correcciones '
                    . 'solicitadas o realizadas.',
            ]);
        }

        $estadoNuevo = $estados[$accion];

        DB::transaction(function () use (
            $convocatoria,
            $revisor,
            $accion,
            $observaciones,
            $estadoNuevo
        ): void {
            // Bloquear el registro para impedir que
            // dos administradores lo revisen simultáneamente.
            $actual = Convocatoria::query()
                ->whereKey($convocatoria->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $estadoRequerido = $accion === 'REENVIAR_REVISION'
                ? 'REQUIERE_CORRECCIONES'
                : 'PENDIENTE_REVISION';

            if ($actual->estado !== $estadoRequerido) {
                throw ValidationException::withMessages([
                    'estado' =>
                        'La convocatoria no está en el estado '
                        . 'requerido. Actualiza la página.',
                ]);
            }

            // Impedir la publicación de convocatorias
            // con una fecha de cierre ya vencida.
            if (
                $accion === 'APROBAR'
                && $actual->fecha_cierre !== null
                && $actual->fecha_cierre->lt(today())
            ) {
                throw ValidationException::withMessages([
                    'fecha_cierre' =>
                        'No se puede publicar una convocatoria '
                        . 'cuya fecha de cierre ya pasó.',
                ]);
            }

            $estadoAnterior = $actual->estado;

            // Actualizar únicamente el estado. No modificar
            // la URL, sus hashes ni los datos extraídos.
            DB::table('convocatorias')
                ->where('id', $actual->id)
                ->update([
                    'estado' => $estadoNuevo,
                    'updated_at' => now(),
                ]);

            // La decisión queda registrada dentro
            // de la misma transacción.
            DB::table(
                'convocatoria_revisiones_administrativas'
            )->insert([
                'convocatoria_id' => $actual->id,
                'revisor_id' => $revisor->id,
                'accion' => $accion,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'observaciones' =>
                    $observaciones !== ''
                        ? $observaciones
                        : null,
                'fecha_decision' => now(),
            ]);
        }, 3);
    }
}
