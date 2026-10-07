<?php

namespace App\Filament\Directivo;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;


    protected string $view =
        'filament.directivo.dashboard';

    protected static ?string $title =
        'Dashboard';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'DIRECTIVO'
            );
    }

    protected function getViewData(): array
    {
        $usuario =
            User::query()
                ->with('role')
                ->whereKey(
                    auth()->id()
                )
                ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Indicadores
        |--------------------------------------------------------------------------
        */

        $porRevisar =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->count();

        $totalConvocatorias =
            Convocatoria::query()
                ->count();

        /*
        | Consideramos revisada una convocatoria
        | cuando ya salió del estado inicial
        | PENDIENTE_REVISION/BORRADOR.
        |
        | No estamos ejecutando aquí ningún
        | flujo de aprobación.
        */
        $revisadas =
            Convocatoria::query()
                ->whereIn(
                    'estado',
                    [
                        'REQUIERE_CORRECCIONES',
                        'PUBLICADA',
                        'ACTIVA',
                        'CERRADA',
                        'DESCARTADA',
                        'ARCHIVADA',
                    ]
                )
                ->count();

        /*
        | Únicamente propuestas cuyo propietario
        | tiene rol DOCENTE.
        |
        | De esta manera no contamos propuestas
        | administrativas/de prueba pertenecientes
        | a otros roles.
        */
        $propuestasDocentes =
            Propuesta::query()
                ->whereHas(
                    'usuario.role',
                    fn ($query) =>
                        $query->where(
                            'nombre',
                            'DOCENTE'
                        )
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Convocatorias pendientes
        |--------------------------------------------------------------------------
        */

        $pendientes =
            Convocatoria::query()
                ->with([
                    'organismo',
                    'fuente',
                    'categoria',
                ])
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc('id')
                ->limit(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Próximos cierres
        |--------------------------------------------------------------------------
        */

        $proximosCierres =
            Convocatoria::query()
                ->with([
                    'organismo',
                ])
                ->whereNotNull(
                    'fecha_cierre'
                )
                ->whereDate(
                    'fecha_cierre',
                    '>=',
                    now()->toDateString()
                )
                ->whereNotIn(
                    'estado',
                    [
                        'CERRADA',
                        'DESCARTADA',
                        'ARCHIVADA',
                    ]
                )
                ->orderBy(
                    'fecha_cierre'
                )
                ->limit(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Actividad basada únicamente en datos reales
        |--------------------------------------------------------------------------
        |
        | No tenemos un historial general de actividad.
        | Por eso mostramos registros recientes reales
        | de convocatorias y propuestas de docentes.
        |--------------------------------------------------------------------------
        */

        $actividadConvocatorias =
            Convocatoria::query()
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(
                    fn (
                        Convocatoria $convocatoria
                    ): array => [
                        'tipo' =>
                            'CONVOCATORIA',

                        'titulo' =>
                            'Convocatoria registrada: '
                            . $convocatoria
                                ->titulo,

                        'fecha' =>
                            $convocatoria
                                ->created_at,
                    ]
                );

        $actividadPropuestas =
            Propuesta::query()
                ->with('usuario')
                ->whereHas(
                    'usuario.role',
                    fn ($query) =>
                        $query->where(
                            'nombre',
                            'DOCENTE'
                        )
                )
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(
                    fn (
                        Propuesta $propuesta
                    ): array => [
                        'tipo' =>
                            'PROPUESTA',

                        'titulo' =>
                            'Propuesta registrada: '
                            . $propuesta->titulo,

                        'fecha' =>
                            $propuesta
                                ->created_at,
                    ]
                );

        $actividadReciente =
            $actividadConvocatorias
                ->concat(
                    $actividadPropuestas
                )
                ->filter(
                    fn (array $item): bool =>
                        $item['fecha']
                        !== null
                )
                ->sortByDesc(
                    fn (array $item): int =>
                        $item['fecha']
                            ->timestamp
                )
                ->take(5)
                ->values();

        return [
            'usuario' =>
                $usuario,

            'porRevisar' =>
                $porRevisar,

            'totalConvocatorias' =>
                $totalConvocatorias,

            'revisadas' =>
                $revisadas,

            'propuestasDocentes' =>
                $propuestasDocentes,

            'pendientes' =>
                $pendientes,

            'proximosCierres' =>
                $proximosCierres,

            'actividadReciente' =>
                $actividadReciente,
        ];
    }
}
