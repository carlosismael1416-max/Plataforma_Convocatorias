<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Registra en bitacora_errores (la «Bitácora de errores» del admin) los
 * fallos de la web. El motor registra los suyos desde el extractor
 * (extractor/app/bitacora.py). Un error igual y sin resolver de los
 * últimos 7 días no se duplica: se marca como repetido.
 */
class Bitacora
{
    public static function registrarExcepcion(Throwable $e): void
    {
        $peticion = app()->runningInConsole() ? null : request();

        static::registrar(
            tipo: 'WEB',
            mensaje: class_basename($e) . ': ' . $e->getMessage(),
            codigo: (string) $e->getCode() ?: null,
            url: $peticion?->fullUrl(),
            detalle: trim(sprintf(
                "%s:%d\nUsuario: %s",
                str_replace(base_path() . '/', '', $e->getFile()),
                $e->getLine(),
                auth()->id() ?? 'sin sesión',
            )),
            traza: mb_substr($e->getTraceAsString(), 0, 6000),
        );
    }

    public static function registrar(
        string $tipo,
        string $mensaje,
        ?string $codigo = null,
        ?string $url = null,
        ?string $detalle = null,
        ?string $traza = null,
    ): void {
        // La bitácora nunca debe provocar otro error (p. ej. sin base de datos).
        try {
            $mensaje = mb_substr(trim($mensaje) ?: 'Error sin mensaje', 0, 2000);

            $existente = DB::table('bitacora_errores')
                ->where('resuelto', false)
                ->where('tipo_error', $tipo)
                ->whereRaw("COALESCE(url, '') = ?", [$url ?? ''])
                ->whereRaw('LEFT(mensaje, 300) = LEFT(?, 300)', [$mensaje])
                ->where('created_at', '>', now()->subDays(7))
                ->orderByDesc('id')
                ->value('id');

            if ($existente) {
                DB::table('bitacora_errores')->where('id', $existente)->update([
                    'updated_at' => now(),
                    'detalle' => DB::raw("RIGHT(COALESCE(detalle, '') || E'\\nSe repitió: ' || to_char(NOW(), 'DD/MM/YYYY HH24:MI'), 4000)"),
                ]);

                return;
            }

            DB::table('bitacora_errores')->insert([
                'tipo_error' => $tipo,
                'codigo_error' => $codigo,
                'mensaje' => $mensaje,
                'detalle' => $detalle,
                'stack_trace' => $traza,
                'url' => $url,
                'resuelto' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable) {
            // Sin bitácora disponible: queda en el log de Laravel.
        }
    }
}
