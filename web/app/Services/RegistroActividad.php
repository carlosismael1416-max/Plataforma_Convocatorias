<?php

namespace App\Services;

use App\Models\Actividad;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Registra lo que hacen los usuarios (tabla actividades). Nunca interrumpe
 * la acción del usuario: si el registro falla, se ignora.
 */
class RegistroActividad
{
    /** Mientras es true, el registro automático de los modelos no anota nada. */
    public static bool $silenciado = false;

    /** Ejecuta $accion sin el registro automático (ya hay uno explícito). */
    public static function sinRegistroAutomatico(callable $accion): mixed
    {
        $antes = static::$silenciado;
        static::$silenciado = true;

        try {
            return $accion();
        } finally {
            static::$silenciado = $antes;
        }
    }

    /**
     * @param  bool  $agrupar  Si el mismo usuario hizo la misma acción sobre
     *                         el mismo registro hace menos de 15 min, solo se
     *                         actualiza la hora (p. ej. guardar varias veces).
     */
    public static function registrar(
        string $accion,
        string $descripcion,
        ?Model $sujeto = null,
        ?int $usuarioId = null,
        bool $agrupar = false,
    ): void {
        try {
            $usuarioId ??= auth()->id();

            if ($usuarioId === null) {
                return;
            }

            $sujetoTipo = $sujeto ? mb_strtolower(class_basename($sujeto)) : null;
            $sujetoId = $sujeto?->getKey();

            if ($agrupar) {
                $previa = Actividad::query()
                    ->where('user_id', $usuarioId)
                    ->where('accion', $accion)
                    ->where('sujeto_tipo', $sujetoTipo)
                    ->where('sujeto_id', $sujetoId)
                    ->where('descripcion', mb_substr($descripcion, 0, 500))
                    ->where('updated_at', '>', now()->subMinutes(15))
                    ->latest('id')
                    ->first();

                if ($previa) {
                    $previa->touch();

                    return;
                }
            }

            Actividad::create([
                'user_id' => $usuarioId,
                'accion' => $accion,
                'descripcion' => mb_substr($descripcion, 0, 500),
                'sujeto_tipo' => $sujetoTipo,
                'sujeto_id' => $sujetoId,
                'ip' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (Throwable) {
            // El registro de actividad nunca debe romper la acción del usuario.
        }
    }
}
