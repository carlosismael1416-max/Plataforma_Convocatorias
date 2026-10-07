<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Estadisticas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Estadísticas';

    protected static ?int $navigationSort = 5;


    protected string $view =
        'filament.directivo.pages.estadisticas';

    protected static ?string $slug =
        'estadisticas';

    public int $anio;

    public string $periodo = 'todo';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'DIRECTIVO'
            );
    }

    public function mount(): void
    {
        $anios =
            $this->obtenerAnios();

        $this->anio =
            $anios->first()
            ?? (int) now()->year;
    }

    public function getTitle(): string
    {
        return 'Estadísticas';
    }

    protected function getViewData(): array
    {
        $anios =
            $this->obtenerAnios();

        [
            $inicio,
            $fin,
        ] = $this->limitesPeriodo();

        /*
        |--------------------------------------------------------------------------
        | Convocatorias del periodo
        |--------------------------------------------------------------------------
        */

        $baseConvocatorias =
            Convocatoria::query()
                ->whereBetween(
                    DB::raw(
                        'COALESCE(fecha_extraccion, created_at)'
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                );

        $totalConvocatorias =
            (clone $baseConvocatorias)
                ->count();

        $pendientes =
            (clone $baseConvocatorias)
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Revisiones administrativas reales
        |--------------------------------------------------------------------------
        */

        $revisadas =
            DB::table(
                'convocatoria_revisiones_administrativas'
            )
                ->whereBetween(
                    'fecha_decision',
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->distinct()
                ->count(
                    'convocatoria_id'
                );

        /*
        |--------------------------------------------------------------------------
        | Propuestas
        |--------------------------------------------------------------------------
        */

        $basePropuestas =
            Propuesta::query()
                ->whereBetween(
                    'created_at',
                    [
                        $inicio,
                        $fin,
                    ]
                );

        $totalPropuestas =
            (clone $basePropuestas)
                ->count();

        $propuestasPorEstado =
            DB::table('propuestas')
                ->whereBetween(
                    'created_at',
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->select(
                    'estado'
                )
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->groupBy(
                    'estado'
                )
                ->orderByDesc(
                    'total'
                )
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Resumen de estados
        |--------------------------------------------------------------------------
        */

        $aprobadas =
            (clone $baseConvocatorias)
                ->whereIn(
                    'estado',
                    [
                        'PUBLICADA',
                        'ACTIVA',
                        'CERRADA',
                    ]
                )
                ->count();

        $correcciones =
            (clone $baseConvocatorias)
                ->where(
                    'estado',
                    'REQUIERE_CORRECCIONES'
                )
                ->count();

        $rechazadas =
            (clone $baseConvocatorias)
                ->where(
                    'estado',
                    'DESCARTADA'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Categorías
        |--------------------------------------------------------------------------
        */

        $categorias =
            DB::table(
                'convocatorias AS c'
            )
                ->leftJoin(
                    'categorias AS cat',
                    'cat.id',
                    '=',
                    'c.categoria_id'
                )
                ->whereBetween(
                    DB::raw(
                        'COALESCE(c.fecha_extraccion, c.created_at)'
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->selectRaw(
                    "
                    COALESCE(
                        NULLIF(BTRIM(cat.nombre), ''),
                        'Sin categoría'
                    ) AS nombre
                    "
                )
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->groupByRaw(
                    "
                    COALESCE(
                        NULLIF(BTRIM(cat.nombre), ''),
                        'Sin categoría'
                    )
                    "
                )
                ->orderByDesc(
                    'total'
                )
                ->get()
                ->map(
                    function (
                        object $fila
                    ) use (
                        $totalConvocatorias
                    ): object {
                        $fila->porcentaje =
                            $totalConvocatorias > 0
                                ? round(
                                    (
                                        (int) $fila->total
                                        /
                                        $totalConvocatorias
                                    )
                                    * 100,
                                    1
                                )
                                : 0;

                        return $fila;
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | Organismos
        |--------------------------------------------------------------------------
        */

        $organismos =
            DB::table(
                'convocatorias AS c'
            )
                ->leftJoin(
                    'organismos AS o',
                    'o.id',
                    '=',
                    'c.organismo_id'
                )
                ->whereBetween(
                    DB::raw(
                        'COALESCE(c.fecha_extraccion, c.created_at)'
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->selectRaw(
                    'o.id AS organismo_id'
                )
                ->selectRaw(
                    "
                    COALESCE(
                        NULLIF(BTRIM(o.nombre), ''),
                        'Sin organismo'
                    ) AS nombre
                    "
                )
                ->selectRaw(
                    'COUNT(*) AS detectadas'
                )
                ->groupBy(
                    'o.id',
                    'o.nombre'
                )
                ->orderByDesc(
                    'detectadas'
                )
                ->get()
                ->map(
                    function (
                        object $fila
                    ) use (
                        $inicio,
                        $fin
                    ): object {
                        $revisionQuery =
                            DB::table(
                                'convocatoria_revisiones_administrativas AS r'
                            )
                                ->join(
                                    'convocatorias AS c',
                                    'c.id',
                                    '=',
                                    'r.convocatoria_id'
                                )
                                ->whereBetween(
                                    'r.fecha_decision',
                                    [
                                        $inicio,
                                        $fin,
                                    ]
                                );

                        $propuestaQuery =
                            DB::table(
                                'propuestas AS p'
                            )
                                ->join(
                                    'convocatorias AS c',
                                    'c.id',
                                    '=',
                                    'p.convocatoria_id'
                                )
                                ->whereBetween(
                                    'p.created_at',
                                    [
                                        $inicio,
                                        $fin,
                                    ]
                                );

                        if (
                            $fila->organismo_id
                            === null
                        ) {
                            $revisionQuery
                                ->whereNull(
                                    'c.organismo_id'
                                );

                            $propuestaQuery
                                ->whereNull(
                                    'c.organismo_id'
                                );
                        } else {
                            $revisionQuery
                                ->where(
                                    'c.organismo_id',
                                    $fila->organismo_id
                                );

                            $propuestaQuery
                                ->where(
                                    'c.organismo_id',
                                    $fila->organismo_id
                                );
                        }

                        $fila->revisadas =
                            $revisionQuery
                                ->distinct()
                                ->count(
                                    'c.id'
                                );

                        $fila->propuestas =
                            $propuestaQuery
                                ->count();

                        return $fila;
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | Convocatorias y revisiones por mes
        |--------------------------------------------------------------------------
        */

        $detectadasPorMes =
            DB::table(
                'convocatorias'
            )
                ->whereBetween(
                    DB::raw(
                        'COALESCE(fecha_extraccion, created_at)'
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->selectRaw(
                    "
                    DATE_TRUNC(
                        'month',
                        COALESCE(
                            fecha_extraccion,
                            created_at
                        )
                    ) AS mes
                    "
                )
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->groupByRaw(
                    "
                    DATE_TRUNC(
                        'month',
                        COALESCE(
                            fecha_extraccion,
                            created_at
                        )
                    )
                    "
                )
                ->get()
                ->mapWithKeys(
                    fn (
                        object $fila
                    ): array => [
                        CarbonImmutable::parse(
                            $fila->mes
                        )->format(
                            'Y-m'
                        ) =>
                            (int) $fila->total,
                    ]
                );

        $revisadasPorMes =
            DB::table(
                'convocatoria_revisiones_administrativas'
            )
                ->whereBetween(
                    'fecha_decision',
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->selectRaw(
                    "
                    DATE_TRUNC(
                        'month',
                        fecha_decision
                    ) AS mes
                    "
                )
                ->selectRaw(
                    "
                    COUNT(
                        DISTINCT convocatoria_id
                    ) AS total
                    "
                )
                ->groupByRaw(
                    "
                    DATE_TRUNC(
                        'month',
                        fecha_decision
                    )
                    "
                )
                ->get()
                ->mapWithKeys(
                    fn (
                        object $fila
                    ): array => [
                        CarbonImmutable::parse(
                            $fila->mes
                        )->format(
                            'Y-m'
                        ) =>
                            (int) $fila->total,
                    ]
                );

        $meses =
            $this->crearMeses(
                $inicio,
                $fin,
                $detectadasPorMes,
                $revisadasPorMes
            );

        /*
        |--------------------------------------------------------------------------
        | Financiamiento
        |--------------------------------------------------------------------------
        */

        $financiamientos =
            DB::table(
                'convocatorias'
            )
                ->whereBetween(
                    DB::raw(
                        'COALESCE(fecha_extraccion, created_at)'
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->whereNotNull(
                    'monto_maximo'
                )
                ->selectRaw(
                    "
                    COALESCE(
                        NULLIF(BTRIM(moneda), ''),
                        'MXN'
                    ) AS moneda
                    "
                )
                ->selectRaw(
                    'SUM(monto_maximo) AS total'
                )
                ->selectRaw(
                    'AVG(monto_maximo) AS promedio'
                )
                ->selectRaw(
                    'MAX(monto_maximo) AS maximo'
                )
                ->selectRaw(
                    'COUNT(*) AS convocatorias'
                )
                ->groupByRaw(
                    "
                    COALESCE(
                        NULLIF(BTRIM(moneda), ''),
                        'MXN'
                    )
                    "
                )
                ->orderBy(
                    'moneda'
                )
                ->get();

        return compact(
            'anios',
            'inicio',
            'fin',
            'totalConvocatorias',
            'revisadas',
            'pendientes',
            'totalPropuestas',
            'categorias',
            'organismos',
            'meses',
            'aprobadas',
            'correcciones',
            'rechazadas',
            'propuestasPorEstado',
            'financiamientos',
        );
    }

    private function obtenerAnios(): Collection
    {
        $convocatorias =
            DB::table(
                'convocatorias'
            )
                ->selectRaw(
                    "
                    EXTRACT(
                        YEAR FROM COALESCE(
                            fecha_extraccion,
                            created_at
                        )
                    )::int AS anio
                    "
                )
                ->distinct()
                ->pluck(
                    'anio'
                );

        $propuestas =
            DB::table(
                'propuestas'
            )
                ->selectRaw(
                    "
                    EXTRACT(
                        YEAR FROM created_at
                    )::int AS anio
                    "
                )
                ->distinct()
                ->pluck(
                    'anio'
                );

        return $convocatorias
            ->merge(
                $propuestas
            )
            ->filter()
            ->map(
                fn (
                    mixed $anio
                ): int =>
                    (int) $anio
            )
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function limitesPeriodo(): array
    {
        $anio =
            (int) $this->anio;

        $inicioAnio =
            CarbonImmutable::create(
                $anio,
                1,
                1,
                0,
                0,
                0
            );

        $finAnio =
            CarbonImmutable::create(
                $anio,
                12,
                31,
                23,
                59,
                59
            );

        $mesReferencia =
            $anio === (int) now()->year
                ? (int) now()->month
                : 12;

        return match (
            $this->periodo
        ) {
            'ultimos6' => [
                CarbonImmutable::create(
                    $anio,
                    max(
                        1,
                        $mesReferencia - 5
                    ),
                    1
                )->startOfDay(),

                CarbonImmutable::create(
                    $anio,
                    $mesReferencia,
                    1
                )->endOfMonth(),
            ],

            'ultimos3' => [
                CarbonImmutable::create(
                    $anio,
                    max(
                        1,
                        $mesReferencia - 2
                    ),
                    1
                )->startOfDay(),

                CarbonImmutable::create(
                    $anio,
                    $mesReferencia,
                    1
                )->endOfMonth(),
            ],

            'mes' => [
                CarbonImmutable::create(
                    $anio,
                    $mesReferencia,
                    1
                )->startOfMonth(),

                CarbonImmutable::create(
                    $anio,
                    $mesReferencia,
                    1
                )->endOfMonth(),
            ],

            default => [
                $inicioAnio,
                $finAnio,
            ],
        };
    }

    private function crearMeses(
        CarbonImmutable $inicio,
        CarbonImmutable $fin,
        Collection $detectadas,
        Collection $revisadas
    ): Collection {
        $nombres = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        $filas =
            collect();

        $cursor =
            $inicio->startOfMonth();

        $ultimo =
            $fin->startOfMonth();

        while (
            $cursor <= $ultimo
        ) {
            $clave =
                $cursor->format(
                    'Y-m'
                );

            $filas->push([
                'clave' =>
                    $clave,

                'nombre' =>
                    $nombres[
                        $cursor->month
                    ],

                'detectadas' =>
                    (int)
                    (
                        $detectadas[
                            $clave
                        ]
                        ?? 0
                    ),

                'revisadas' =>
                    (int)
                    (
                        $revisadas[
                            $clave
                        ]
                        ?? 0
                    ),
            ]);

            $cursor =
                $cursor->addMonth();
        }

        $maximo =
            max(
                1,
                (int)
                $filas
                    ->flatMap(
                        fn (
                            array $fila
                        ): array => [
                            $fila[
                                'detectadas'
                            ],
                            $fila[
                                'revisadas'
                            ],
                        ]
                    )
                    ->max()
            );

        return $filas
            ->map(
                function (
                    array $fila
                ) use (
                    $maximo
                ): array {
                    $fila[
                        'altura_detectadas'
                    ] =
                        $fila[
                            'detectadas'
                        ] > 0
                            ? max(
                                4,
                                round(
                                    (
                                        $fila[
                                            'detectadas'
                                        ]
                                        /
                                        $maximo
                                    )
                                    * 100,
                                    1
                                )
                            )
                            : 0;

                    $fila[
                        'altura_revisadas'
                    ] =
                        $fila[
                            'revisadas'
                        ] > 0
                            ? max(
                                4,
                                round(
                                    (
                                        $fila[
                                            'revisadas'
                                        ]
                                        /
                                        $maximo
                                    )
                                    * 100,
                                    1
                                )
                            )
                            : 0;

                    return $fila;
                }
            );
    }
}
