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
 * Dashboard directivo según el diseño de Figma (DashboardDirectivo). El
 * directivo revisa las propuestas para participar; las convocatorias se
 * publican solas. Todo con datos reales.
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

        $propuestasPorRevisar = Propuesta::query()->where('estado', 'ENVIADA')->count();

        $enviadasMes = PropuestaRevision::query()
            ->where('created_at', '>=', $inicioMes)
            ->where('accion', 'ENVIAR')
            ->count();
        $aprobadasMes = (clone $dictamenesMes)->where('accion', 'APROBAR')->count();
        $devueltasMes = (clone $dictamenesMes)->whereIn('accion', ['DEVOLVER', 'RECHAZAR'])->count();
        $publicadasMes = Convocatoria::query()
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA', 'CERRADA'])
            ->where('created_at', '>=', $inicioMes)
            ->count();

        return [
            'nombre' => trim($usuario->name . ' ' . ($usuario->apellidos ?? '')),
            'departamento' => $usuario->departamento?->nombre,
            'fecha' => $hoy->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),

            'urls' => [
                'propuestas' => Pages\PropuestasPorRevisar::getUrl(),
                'buscar' => Pages\BuscarConvocatorias::getUrl(),
                'subir' => ConvocatoriaResource::getUrl('create'),
                'reportes' => Pages\Reportes::getUrl(),
                'estadisticas' => Pages\Estadisticas::getUrl(),
                'calendario' => Pages\Calendario::getUrl(),
            ],

            'estadisticas' => [
                [
                    'etiqueta' => 'Propuestas por revisar',
                    'valor' => $propuestasPorRevisar,
                    'sub' => $propuestasPorRevisar > 0 ? 'Acción requerida' : 'Sin pendientes',
                    'icono' => 'portapapeles',
                    'color' => 'ambar',
                    'url' => Pages\PropuestasPorRevisar::getUrl(),
                ],
                [
                    'etiqueta' => 'Aprobadas este mes',
                    'valor' => $aprobadasMes,
                    'sub' => 'Propuestas dictaminadas',
                    'icono' => 'aprobado',
                    'color' => 'verde',
                    'url' => Pages\PropuestasPorRevisar::getUrl(),
                ],
                [
                    'etiqueta' => 'Devueltas o rechazadas',
                    'valor' => $devueltasMes,
                    'sub' => 'Este mes, con observaciones',
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

            'propuestasPendientes' => Propuesta::query()
                ->with(['usuario:id,name,apellidos', 'convocatoria:id,titulo'])
                ->where('estado', 'ENVIADA')
                ->orderBy('fecha_envio')
                ->limit(6)
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

            'recientes' => $disponibles()
                ->with(['organismo:id,nombre'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Convocatoria $c): array => [
                    'titulo' => $c->titulo,
                    'organismo' => $c->organismo?->nombre ?? 'Sin organismo',
                    'manual' => $c->origen === 'MANUAL',
                    'publicada' => $c->created_at?->locale('es')->isoFormat('D MMM YYYY'),
                    'cierre' => $c->fecha_cierre?->locale('es')->isoFormat('D MMM YYYY') ?? 'Sin fecha',
                    'url' => Pages\DetalleConvocatoria::getUrl(['record' => $c->id]),
                ])
                ->all(),

            'resumen' => [
                ['etiqueta' => 'Convocatorias publicadas', 'valor' => $publicadasMes, 'max' => max($publicadasMes, 1), 'color' => '#1A4B8C'],
                ['etiqueta' => 'Propuestas enviadas', 'valor' => $enviadasMes, 'max' => max($enviadasMes, 1), 'color' => '#7C3AED'],
                ['etiqueta' => 'Aprobadas', 'valor' => $aprobadasMes, 'max' => max($enviadasMes, $aprobadasMes, 1), 'color' => '#059669'],
                ['etiqueta' => 'Devueltas o rechazadas', 'valor' => $devueltasMes, 'max' => max($enviadasMes, $devueltasMes, 1), 'color' => '#DC2626'],
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
