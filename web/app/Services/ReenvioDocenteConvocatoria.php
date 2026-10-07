<?php

namespace App\Services;

use App\Models\Convocatoria;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReenvioDocenteConvocatoria
{
    public static function registrar(
        Convocatoria $convocatoria,
        User $docente,
        string $observaciones
    ): void {
        if (
            ! $docente->estado
            || ! $docente->tieneRol('DOCENTE')
        ) {
            throw new AuthorizationException(
                'No tienes permiso para reenviar convocatorias.'
            );
        }

        $observaciones = trim($observaciones);

        if (
            mb_strlen($observaciones) < 5
            || mb_strlen($observaciones) > 5000
        ) {
            throw ValidationException::withMessages([
                'observaciones' =>
                    'Describe las correcciones realizadas '
                    . 'entre 5 y 5000 caracteres.',
            ]);
        }

        DB::transaction(function () use (
            $convocatoria,
            $docente,
            $observaciones
        ): void {
            $actual = Convocatoria::query()
                ->whereKey($convocatoria->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // El docente solo puede reenviar
            // convocatorias que él mismo haya subido.
            if (
                (int) $actual->subido_por_user_id
                    !== (int) $docente->getKey()
                || $actual->origen !== 'MANUAL'
            ) {
                throw new AuthorizationException(
                    'Esta convocatoria no te pertenece.'
                );
            }

            if (
                $actual->estado
                    !== 'REQUIERE_CORRECCIONES'
            ) {
                throw ValidationException::withMessages([
                    'estado' =>
                        'La convocatoria no requiere correcciones '
                        . 'o ya fue reenviada. Actualiza la página.',
                ]);
            }

            DB::table('convocatorias')
                ->where('id', $actual->id)
                ->update([
                    'estado' => 'PENDIENTE_REVISION',
                    'updated_at' => now(),
                ]);

            DB::table(
                'convocatoria_revisiones_administrativas'
            )->insert([
                'convocatoria_id' => $actual->id,
                'revisor_id' => $docente->getKey(),
                'accion' => 'REENVIAR_REVISION',
                'estado_anterior' => 'REQUIERE_CORRECCIONES',
                'estado_nuevo' => 'PENDIENTE_REVISION',
                'observaciones' => $observaciones,
                'fecha_decision' => now(),
            ]);
        }, 3);
    }
}
