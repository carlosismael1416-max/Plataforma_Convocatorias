<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\NotificacionPreferencia;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Illuminate\Support\Carbon;

/**
 * Notificaciones generadas por la plataforma, respetando las preferencias
 * de cada usuario (notificacion_preferencias):
 *
 * - fechas_proximas: convocatorias guardadas y propuestas en preparación
 *   cuya convocatoria cierra en los próximos días.
 * - nuevas_propuestas: envíos (para directivos) y dictámenes (para autores).
 * - convocatorias_revision: cambios detectados en convocatorias que el
 *   usuario sigue (los registra el seguimiento del extractor).
 */
class Avisos
{
    /** Días de anticipación para avisar un cierre. */
    public const DIAS_AVISO_CIERRE = 7;

    /** Valores por defecto de la migración cuando el usuario no tiene fila. */
    private const POR_DEFECTO = [
        'convocatorias_revision' => true,
        'fechas_proximas' => true,
        'nuevas_propuestas' => true,
        'avisos_sistema' => false,
    ];

    public static function quiere(int $usuarioId, string $preferencia): bool
    {
        $valor = NotificacionPreferencia::query()
            ->where('user_id', $usuarioId)
            ->value($preferencia);

        return $valor === null
            ? self::POR_DEFECTO[$preferencia] ?? false
            : (bool) $valor;
    }

    /**
     * Crea (una sola vez) los avisos de cierre próximo del usuario. Se llama
     * al abrir el dashboard o las notificaciones: no hay tareas programadas.
     */
    public static function generarAvisosDeCierre(User $usuario): void
    {
        if (! self::quiere($usuario->id, 'fechas_proximas')) {
            return;
        }

        $hoy = Carbon::today();
        $limite = $hoy->copy()->addDays(self::DIAS_AVISO_CIERRE);

        $porCerrar = fn ($consulta) => $consulta
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
            ->whereNotNull('fecha_cierre')
            ->whereDate('fecha_cierre', '>=', $hoy)
            ->whereDate('fecha_cierre', '<=', $limite);

        $guardadas = UsuarioConvocatoria::query()
            ->where('user_id', $usuario->id)
            ->whereHas('convocatoria', $porCerrar)
            ->with('convocatoria:id,titulo,fecha_cierre')
            ->get();

        foreach ($guardadas as $seguimiento) {
            $c = $seguimiento->convocatoria;

            self::crearUnaVez($usuario->id, 'CONVOCATORIA_POR_CERRAR', [
                'convocatoria_id' => $c->id,
                'titulo' => 'Convocatoria por cerrar',
                'mensaje' => '«' . $c->titulo . '» cierra el '
                    . $c->fecha_cierre->locale('es')->isoFormat('D [de] MMMM') . '.',
            ]);
        }

        $propuestas = Propuesta::query()
            ->where('user_id', $usuario->id)
            ->whereIn('estado', RevisionPropuesta::EDITABLES)
            ->whereHas('convocatoria', $porCerrar)
            ->with('convocatoria:id,titulo,fecha_cierre')
            ->get();

        foreach ($propuestas as $p) {
            self::crearUnaVez($usuario->id, 'PROPUESTA_POR_VENCER', [
                'propuesta_id' => $p->id,
                'convocatoria_id' => $p->convocatoria_id,
                'titulo' => 'Tu propuesta aún no se envía',
                'mensaje' => '«' . $p->titulo . '»: la convocatoria cierra el '
                    . $p->convocatoria->fecha_cierre->locale('es')->isoFormat('D [de] MMMM') . '.',
            ]);
        }
    }

    public static function notificar(int $usuarioId, string $preferencia, array $datos): void
    {
        if (! self::quiere($usuarioId, $preferencia)) {
            return;
        }

        Notificacion::create($datos + ['user_id' => $usuarioId, 'leida' => false]);
    }

    private static function crearUnaVez(int $usuarioId, string $tipo, array $datos): void
    {
        $clave = array_intersect_key($datos, array_flip(['convocatoria_id', 'propuesta_id']));

        $existe = Notificacion::query()
            ->where('user_id', $usuarioId)
            ->where('tipo', $tipo)
            ->where($clave)
            ->exists();

        if (! $existe) {
            Notificacion::create($datos + [
                'user_id' => $usuarioId,
                'tipo' => $tipo,
                'leida' => false,
            ]);
        }
    }
}
