<?php

namespace App\Filament\Admin;

use App\Filament\Admin\Pages\BitacoraErrores;
use App\Filament\Admin\Pages\DetalleEjecucion;
use App\Filament\Admin\Pages\MotorExtraccion;
use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Filament\Resources\Fuentes\FuenteResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Concerns\ControlaMotor;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Dashboard del administrador según el diseño de Figma (DashboardAdmin):
 * banner, estadísticas, tarjeta del motor con el último ciclo automático,
 * historial de ejecuciones, gestión rápida y estado del sistema. Datos reales.
 */
class Dashboard extends BaseDashboard
{
    use ControlaMotor;

    protected string $view = 'filament.admin.dashboard';

    protected static ?string $title = 'Dashboard';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('ADMINISTRADOR');
    }

    protected function getViewData(): array
    {
        /** @var User $usuario */
        $usuario = auth()->user();
        $hoy = Carbon::today();

        $fuentes = DB::table('fuentes')->count();
        $fuentesActivas = DB::table('fuentes')->where('activa', true)->count();

        $erroresPendientes = DB::table('bitacora_errores')
            ->where(fn ($q) => $q->where('resuelto', false)->orWhereNull('resuelto'))
            ->count();

        $ciclo = $this->ultimoCiclo();
        $apiDisponible = $this->apiDisponible();

        // Nuevas y visibles para todos (mismo criterio que el docente).
        $nuevasSemana = \App\Models\Convocatoria::query()
            ->visibles()
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return $this->datosInterruptor() + [
            'nombre' => trim($usuario->name . ' ' . ($usuario->apellidos ?? '')),
            'fecha' => $hoy->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),

            'urls' => [
                'motor' => MotorExtraccion::getUrl(),
                'usuarios' => UserResource::getUrl('index'),
                'convocatorias' => ConvocatoriaResource::getUrl('index'),
                'fuentes' => FuenteResource::getUrl('index'),
                'bitacora' => BitacoraErrores::getUrl(),
            ],

            'estadisticas' => [
                [
                    'etiqueta' => 'Fuentes activas',
                    'valor' => $fuentesActivas,
                    'sub' => "De {$fuentes} registradas",
                    'icono' => 'globo',
                    'color' => 'azul',
                    'url' => FuenteResource::getUrl('index'),
                ],
                [
                    'etiqueta' => 'Último ciclo',
                    'valor' => $ciclo ? $ciclo['inicio']->format('H:i') : '—',
                    'sub' => $ciclo ? $ciclo['inicio']->locale('es')->isoFormat('D MMM YYYY') : 'Sin reporte',
                    'icono' => 'reloj',
                    'color' => 'verde',
                    'url' => MotorExtraccion::getUrl(),
                ],
                [
                    'etiqueta' => 'Convocatorias nuevas',
                    'valor' => $nuevasSemana,
                    'sub' => 'Publicadas y vigentes, últimos 7 días',
                    'icono' => 'libro',
                    'color' => 'morado',
                    'url' => ConvocatoriaResource::getUrl('index'),
                ],
                [
                    'etiqueta' => 'Errores pendientes',
                    'valor' => $erroresPendientes,
                    'sub' => 'Sin resolver en la bitácora',
                    'icono' => 'alerta',
                    'color' => 'rojo',
                    'url' => BitacoraErrores::getUrl(),
                ],
            ],

            'ciclo' => $ciclo,
            'apiDisponible' => $apiDisponible,

            'ejecuciones' => DB::table('ejecuciones_scraping')
                ->orderByDesc('fecha_inicio')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(fn ($e): array => [
                    'id' => $e->id,
                    'inicio' => $e->fecha_inicio
                        ? Carbon::parse($e->fecha_inicio)->locale('es')->isoFormat('D MMM YYYY, HH:mm')
                        : '—',
                    'estado' => $e->estado,
                    'nuevas' => (int) $e->nuevas_convocatorias,
                    'actualizadas' => (int) $e->convocatorias_actualizadas,
                    'errores' => (int) $e->total_errores,
                    'url' => DetalleEjecucion::getUrl(['record' => $e->id]),
                ])
                ->all(),

            'sistema' => [
                ['etiqueta' => 'API del extractor', 'estado' => $apiDisponible ? 'ok' : 'mal', 'texto' => $apiDisponible ? 'OK' : 'Sin respuesta'],
                ['etiqueta' => 'Base de datos', 'estado' => 'ok', 'texto' => 'OK'],
                [
                    'etiqueta' => 'Ciclo automático (cada 6 h)',
                    // Con advertencias el ciclo sí terminó: alguna fase tuvo errores
                    // (quedan en la bitácora). Es aviso, no falla.
                    'estado' => $ciclo === null ? 'mal' : ($ciclo['atrasado'] ? 'aviso' : match ($ciclo['estado']) {
                        'COMPLETADO', 'APAGADO' => 'ok',
                        'COMPLETADO_CON_ADVERTENCIAS' => 'aviso',
                        default => 'mal',
                    }),
                    'texto' => $ciclo === null ? 'Sin reporte' : ($ciclo['atrasado'] ? 'Atrasado' : $ciclo['etiqueta']),
                    'url' => $ciclo !== null && $ciclo['estado'] !== 'COMPLETADO' ? BitacoraErrores::getUrl() : null,
                ],
                [
                    'etiqueta' => 'Bitácora de errores',
                    'estado' => $erroresPendientes > 0 ? 'aviso' : 'ok',
                    'texto' => $erroresPendientes > 0 ? "{$erroresPendientes} pendiente(s)" : 'Sin pendientes',
                ],
            ],
        ];
    }

    /**
     * Último ciclo automático según el reporte que deja el extractor
     * (extractor/ciclo_automatico_ultimo.json). Null si no existe o no se lee.
     */
    private function ultimoCiclo(): ?array
    {
        $ruta = base_path('../extractor/ciclo_automatico_ultimo.json');

        try {
            $datos = is_readable($ruta) ? json_decode((string) file_get_contents($ruta), true) : null;
        } catch (\Throwable) {
            $datos = null;
        }

        if (! is_array($datos) || empty($datos['inicio'])) {
            return null;
        }

        // La app guarda en UTC; el ciclo se muestra en hora local (Yucatán).
        $inicio = Carbon::parse($datos['inicio'])->setTimezone('America/Merida');
        $fin = ! empty($datos['fin']) ? Carbon::parse($datos['fin']) : null;

        $errores = (int) ($datos['publicacion']['errores'] ?? 0)
            + (int) ($datos['revalidacion']['errores'] ?? 0)
            + (int) ($datos['fuentes']['errores'] ?? 0)
            + (int) ($datos['seguimiento']['errores'] ?? 0)
            + (($datos['descubrimiento']['ok'] ?? true) ? 0 : 1);

        $estado = (string) ($datos['estado'] ?? 'DESCONOCIDO');

        return [
            'estado' => $estado,
            'etiqueta' => [
                'COMPLETADO' => 'Completado',
                'COMPLETADO_CON_ADVERTENCIAS' => 'Completado con advertencias',
                'FALLIDO' => 'Falló',
                'APAGADO' => 'Motor apagado',
                'YA_EN_EJECUCION' => 'En ejecución',
            ][$estado] ?? ucfirst(mb_strtolower(str_replace('_', ' ', $estado))),
            'mensaje' => $datos['mensaje'] ?? null,
            'inicio' => $inicio,
            'duracion' => $fin ? max(1, (int) round($inicio->diffInSeconds($fin))) : null,
            // El timer corre cada 6 h; con más de 7 h sin ciclo algo falla (o WSL estuvo apagado).
            'atrasado' => $inicio->lt(now()->subHours(7)),
            'cifras' => [
                ['etiqueta' => 'Evaluados', 'valor' => (int) ($datos['evaluacion']['evaluados'] ?? 0), 'color' => '#1A4B8C'],
                ['etiqueta' => 'Cambios avisados', 'valor' => (int) ($datos['seguimiento']['con_cambios'] ?? 0), 'color' => '#334155'],
                ['etiqueta' => 'A revisión', 'valor' => (int) ($datos['evaluacion']['revision'] ?? 0), 'color' => '#D97706'],
                ['etiqueta' => 'Publicadas', 'valor' => (int) ($datos['publicacion']['publicados'] ?? 0) + (int) ($datos['revalidacion']['publicadas'] ?? 0), 'color' => '#059669'],
                ['etiqueta' => 'Fuentes oficiales', 'valor' => (int) ($datos['fuentes']['consultadas'] ?? 0), 'color' => '#64748B'],
                ['etiqueta' => 'Errores', 'valor' => $errores, 'color' => '#DC2626'],
            ],
        ];
    }

    private function apiDisponible(): bool
    {
        try {
            return Http::timeout(2)
                ->get(rtrim((string) config('services.extractor.url', 'http://127.0.0.1:8001'), '/') . '/health')
                ->json('status') === 'ok';
        } catch (\Throwable) {
            return false;
        }
    }
}
