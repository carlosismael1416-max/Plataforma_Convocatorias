<?php

namespace App\Filament\Directivo;

use App\Filament\Directivo\Pages;
use App\Filament\Directivo\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\PropuestaRevision;
use App\Models\User;
use App\Services\Avisos;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;

/**
 * Dashboard directivo según el diseño de Figma (DashboardDirectivo): banner,
 * estadísticas, pendientes de revisión, resumen del mes y accesos rápidos.
 * Todo con datos reales.
 */
class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.directivo.dashboard';

    protected static ?string $title = 'Dashboard';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DIRECTIVO');
    }

    protected function getViewData(): array
    {
        /** @var User $usuario */
        $usuario = auth()->user();
        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();

        Avisos::generarAvisosDeCierre($usuario);

        $disponibles = fn () => Convocatoria::query()
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
            ->noVencidas();

        $dictamenesMes = PropuestaRevision::query()
            ->where('created_at', '>=', $inicioMes)
            ->whereIn('accion', ['APROBAR', 'DEVOLVER', 'RECHAZAR']);

        $porRevisar = Convocatoria::query()->where('estado', 'PENDIENTE_REVISION')->count();
        $propuestasPorRevisar = Propuesta::query()->where('estado', 'ENVIADA')->count();

        $detectadasMes = Convocatoria::query()->where('created_at', '>=', $inicioMes)->count();
        $publicadasMes = Convocatoria::query()
            ->where('created_at', '>=', $inicioMes)
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA', 'CERRADA'])
            ->count();
        $pendientesMes = Convocatoria::query()
            ->where('created_at', '>=', $inicioMes)
            ->where('estado', 'PENDIENTE_REVISION')
            ->count();

        return [
            'nombre' => trim($usuario->name . ' ' . ($usuario->apellidos ?? '')),
            'departamento' => $usuario->departamento?->nombre,
            'fecha' => $hoy->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),

            'urls' => [
                'porRevisar' => Pages\ConvocatoriasPorRevisar::getUrl(),
                'propuestas' => Pages\PropuestasPorRevisar::getUrl(),
                'subir' => ConvocatoriaResource::getUrl('create'),
                'reportes' => Pages\Reportes::getUrl(),
                'estadisticas' => Pages\Estadisticas::getUrl(),
                'calendario' => Pages\Calendario::getUrl(),
                'buscar' => Pages\BuscarConvocatorias::getUrl(),
            ],

            'estadisticas' => [
                [
                    'etiqueta' => 'Convocatorias por revisar',
                    'valor' => $porRevisar,
                    'sub' => $porRevisar > 0 ? 'Acción requerida' : 'Sin pendientes',
                    'icono' => 'portapapeles',
                    'color' => 'ambar',
                    'url' => Pages\ConvocatoriasPorRevisar::getUrl(),
                ],
                [
                    'etiqueta' => 'Propuestas por revisar',
                    'valor' => $propuestasPorRevisar,
                    'sub' => (clone $dictamenesMes)->where('accion', 'APROBAR')->count() . ' aprobada(s) este mes',
                    'icono' => 'aprobado',
                    'color' => 'verde',
                    'url' => Pages\PropuestasPorRevisar::getUrl(),
                ],
                [
                    'etiqueta' => 'Propuestas rechazadas',
                    'valor' => Propuesta::query()->where('estado', 'RECHAZADA')->count(),
                    'sub' => 'Con observaciones',
                    'icono' => 'rechazado',
                    'color' => 'rojo',
                    'url' => Pages\PropuestasPorRevisar::getUrl(),
                ],
                [
                    'etiqueta' => 'Convocatorias activas',
                    'valor' => $disponibles()->count(),
                    'sub' => 'Publicadas y vigentes',
                    'icono' => 'libro',
                    'color' => 'azul',
                    'url' => Pages\BuscarConvocatorias::getUrl(),
                ],
            ],

            'pendientes' => Convocatoria::query()
                ->with(['organismo:id,nombre', 'categoria:id,nombre'])
                ->where('estado', 'PENDIENTE_REVISION')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Convocatoria $c): array => [
                    'titulo' => $c->titulo,
                    'organismo' => $c->organismo?->nombre ?? 'Sin organismo',
                    'categoria' => $c->categoria?->nombre,
                    'manual' => $c->origen === 'MANUAL',
                    'encontrada' => $c->created_at?->locale('es')->isoFormat('D MMM YYYY'),
                    'cierre' => $c->fecha_cierre?->locale('es')->isoFormat('D MMM YYYY') ?? 'Sin fecha',
                    'url' => Pages\RevisarConvocatoria::getUrl(['record' => $c->id]),
                ])
                ->all(),

            'propuestasPendientes' => Propuesta::query()
                ->with(['usuario:id,name,apellidos', 'convocatoria:id,titulo'])
                ->where('estado', 'ENVIADA')
                ->orderBy('fecha_envio')
                ->limit(5)
                ->get()
                ->map(fn (Propuesta $p): array => [
                    'titulo' => $p->titulo,
                    'autor' => trim(($p->usuario?->name ?? '') . ' ' . ($p->usuario?->apellidos ?? '')),
                    'propia' => $p->user_id === $usuario->id,
                    'convocatoria' => $p->convocatoria?->titulo,
                    'enviada' => $p->fecha_envio?->locale('es')->isoFormat('D MMM YYYY'),
                    'url' => Pages\RevisarPropuesta::getUrl(['propuesta' => $p->id]),
                ])
                ->all(),

            'resumen' => [
                ['etiqueta' => 'Detectadas este mes', 'valor' => $detectadasMes, 'max' => max($detectadasMes, 1), 'color' => '#1A4B8C'],
                ['etiqueta' => 'Ya revisadas', 'valor' => $publicadasMes, 'max' => max($detectadasMes, 1), 'color' => '#059669'],
                ['etiqueta' => 'Siguen pendientes', 'valor' => $pendientesMes, 'max' => max($detectadasMes, 1), 'color' => '#D97706'],
                ['etiqueta' => 'Dictámenes de propuestas', 'valor' => (clone $dictamenesMes)->count(), 'max' => max((clone $dictamenesMes)->count(), 1), 'color' => '#7C3AED'],
            ],

            'proximosCierres' => $disponibles()
                ->whereNotNull('fecha_cierre')
                ->orderBy('fecha_cierre')
                ->limit(4)
                ->get(['id', 'titulo', 'fecha_cierre'])
                ->map(fn (Convocatoria $c): array => [
                    'titulo' => $c->titulo,
                    'dias' => (int) $hoy->diffInDays($c->fecha_cierre),
                    'cierre' => $c->fecha_cierre->locale('es')->isoFormat('D MMM YYYY'),
                    'url' => Pages\DetalleConvocatoria::getUrl(['record' => $c->id]),
                ])
                ->all(),
        ];
    }
}
