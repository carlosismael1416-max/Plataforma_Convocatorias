<?php

namespace App\Filament\Admin;

use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected string $view =
        'filament.admin.dashboard';

    protected static ?string $title =
        'Dashboard';

    protected static ?string $navigationLabel =
        'Dashboard';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    protected function getViewData(): array
    {
        $usuarios =
            DB::table('users')
                ->count();

        $usuariosPorRol =
            DB::table('users')
                ->join(
                    'roles',
                    'roles.id',
                    '=',
                    'users.role_id'
                )
                ->select(
                    'roles.nombre',
                    DB::raw(
                        'COUNT(*) AS total'
                    )
                )
                ->groupBy(
                    'roles.nombre'
                )
                ->pluck(
                    'total',
                    'nombre'
                );

        $convocatorias =
            DB::table('convocatorias')
                ->count();

        $pendientesRevision =
            DB::table('convocatorias')
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->count();

        $fuentes =
            DB::table('fuentes')
                ->count();

        $fuentesActivas =
            DB::table('fuentes')
                ->where(
                    'activa',
                    true
                )
                ->count();

        $alertas =
            DB::table(
                'bitacora_errores'
            )
                ->where(
                    function ($query) {
                        $query
                            ->where(
                                'resuelto',
                                false
                            )
                            ->orWhereNull(
                                'resuelto'
                            );
                    }
                )
                ->count();

        $ejecuciones =
            DB::table(
                'ejecuciones_scraping'
            )
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(4)
                ->get()
                ->map(
                    fn ($ejecucion) =>
                        $this->presentarEjecucion(
                            $ejecucion
                        )
                );

        $fuentesListado =
            DB::table('fuentes')
                ->orderByDesc(
                    'activa'
                )
                ->orderBy(
                    'nombre'
                )
                ->limit(5)
                ->get([
                    'id',
                    'nombre',
                    'url_base',
                    'tipo_fuente',
                    'activa',
                    'ultima_ejecucion',
                ]);

        $errores =
            DB::table(
                'bitacora_errores'
            )
                ->where(
                    function ($query) {
                        $query
                            ->where(
                                'resuelto',
                                false
                            )
                            ->orWhereNull(
                                'resuelto'
                            );
                    }
                )
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(4)
                ->get()
                ->map(
                    fn ($error) =>
                        $this->presentarError(
                            $error
                        )
                );

        $ultimaEjecucion =
            DB::table(
                'ejecuciones_scraping'
            )
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc(
                    'id'
                )
                ->first();

        return [
            'usuarios' =>
                $usuarios,

            'usuariosPorRol' =>
                $usuariosPorRol,

            'convocatorias' =>
                $convocatorias,

            'pendientesRevision' =>
                $pendientesRevision,

            'fuentes' =>
                $fuentes,

            'fuentesActivas' =>
                $fuentesActivas,

            'alertas' =>
                $alertas,

            'ejecuciones' =>
                $ejecuciones,

            'fuentesListado' =>
                $fuentesListado,

            'errores' =>
                $errores,

            'ultimaEjecucion' =>
                $ultimaEjecucion,
        ];
    }

    private function presentarEjecucion(
        object $ejecucion
    ): array {
        $estado =
            strtoupper(
                (string)
                $ejecucion->estado
            );

        $estadoTexto =
            match ($estado) {
                'INICIADO' =>
                    'Iniciado',

                'EJECUTANDO' =>
                    'Ejecutando',

                'COMPLETADO' =>
                    'Completado',

                'COMPLETADO_CON_ERRORES' =>
                    'Con errores',

                'FALLIDO' =>
                    'Fallido',

                default =>
                    $estado !== ''
                        ? $estado
                        : 'Sin estado',
            };

        $estadoClase =
            match ($estado) {
                'COMPLETADO' =>
                    'status-success',

                'INICIADO',
                'EJECUTANDO',
                'COMPLETADO_CON_ERRORES' =>
                    'status-warning',

                'FALLIDO' =>
                    'status-danger',

                default =>
                    'status-neutral',
            };

        $fecha =
            $ejecucion->fecha_inicio
                ? Carbon::parse(
                    $ejecucion
                        ->fecha_inicio
                )
                    ->locale('es')
                    ->diffForHumans()
                : 'Sin fecha';

        $detalle =
            sprintf(
                '%d fuentes · %d encontradas · %d nuevas · %d errores',
                (int)
                $ejecucion->total_fuentes,
                (int)
                $ejecucion->total_encontradas,
                (int)
                $ejecucion->nuevas_convocatorias,
                (int)
                $ejecucion->total_errores
            );

        return [
            'id' =>
                $ejecucion->id,

            'titulo' =>
                'Ciclo de extracción',

            'detalle' =>
                $detalle,

            'fecha' =>
                $fecha,

            'estado' =>
                $estadoTexto,

            'clase' =>
                $estadoClase,

            'url' =>
                route(
                    'filament.admin.pages.detalle-ejecucion',
                    [
                        'record' =>
                            $ejecucion->id,
                    ]
                ),
        ];
    }

    private function presentarError(
        object $error
    ): array {
        $tipo =
            trim(
                (string)
                $error->tipo_error
            );

        $titulo =
            $tipo !== ''
                ? str_replace(
                    '_',
                    ' ',
                    $tipo
                )
                : 'Error del sistema';

        return [
            'id' =>
                $error->id,

            'titulo' =>
                ucfirst(
                    mb_strtolower(
                        $titulo
                    )
                ),

            'mensaje' =>
                $error->mensaje
                ?: 'Sin descripción disponible.',

            'fecha' =>
                $error->created_at
                    ? Carbon::parse(
                        $error
                            ->created_at
                    )
                        ->locale('es')
                        ->diffForHumans()
                    : 'Sin fecha',

            'url' =>
                route(
                    'filament.admin.pages.detalle-error',
                    [
                        'record' =>
                            $error->id,
                    ]
                ),
        ];
    }
}
