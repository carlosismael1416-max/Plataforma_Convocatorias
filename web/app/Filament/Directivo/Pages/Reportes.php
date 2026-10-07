<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Categoria;
use App\Models\Convocatoria;
use App\Models\Organismo;
use App\Models\Propuesta;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class Reportes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?int $navigationSort = 6;


    protected string $view =
        'filament.directivo.pages.reportes';

    protected static ?string $slug =
        'reportes';

    public string $tipoReporte =
        'convocatorias';

    public string $formato =
        'pdf';

    public string $fechaInicio = '';

    public string $fechaFinal = '';

    public string $categoria = '';

    public string $organismo = '';

    public string $estado = '';

    public bool $incluirDatosGenerales = true;

    public bool $incluirOrganismos = true;

    public bool $incluirFechas = true;

    public bool $incluirMontos = true;

    public bool $incluirRequisitos = false;

    public bool $incluirDocumentos = false;

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
        $this->fechaInicio =
            now()
                ->startOfYear()
                ->toDateString();

        $this->fechaFinal =
            now()
                ->endOfYear()
                ->toDateString();

        $seccion =
            strtolower(
                trim(
                    (string)
                    request()->query(
                        'seccion',
                        ''
                    )
                )
            );

        if (
            in_array(
                $seccion,
                [
                    'convocatorias',
                    'revisiones',
                    'propuestas',
                    'financiamiento',
                ],
                true
            )
        ) {
            $this->tipoReporte =
                $seccion;
        }
    }

    public function getTitle(): string
    {
        return 'Reportes';
    }

    public function probarBoton(): void
    {
        Notification::make()
            ->title('Livewire funcionando')
            ->body('El botón llegó correctamente al componente Reportes.')
            ->success()
            ->send();
    }

    public function seleccionarTipo(
        string $tipo
    ): void {
        if (
            ! in_array(
                $tipo,
                [
                    'convocatorias',
                    'revisiones',
                    'propuestas',
                    'financiamiento',
                ],
                true
            )
        ) {
            return;
        }

        $this->tipoReporte =
            $tipo;

        $this->estado = '';
    }

    public function seleccionarFormato(
        string $formato
    ): void {
        if (
            ! in_array(
                $formato,
                [
                    'pdf',
                    'xlsx',
                    'csv',
                ],
                true
            )
        ) {
            return;
        }

        $this->formato =
            $formato;
    }

    public function limpiarFiltros(): void
    {
        $this->fechaInicio =
            now()
                ->startOfYear()
                ->toDateString();

        $this->fechaFinal =
            now()
                ->endOfYear()
                ->toDateString();

        $this->categoria = '';
        $this->organismo = '';
        $this->estado = '';

        $this->incluirDatosGenerales = true;
        $this->incluirOrganismos = true;
        $this->incluirFechas = true;
        $this->incluirMontos = true;
        $this->incluirRequisitos = false;
        $this->incluirDocumentos = false;
    }

    protected function getViewData(): array
    {
        return [
            'categorias' =>
                Categoria::query()
                    ->orderBy('nombre')
                    ->get(),

            'organismos' =>
                Organismo::query()
                    ->orderBy('nombre')
                    ->get(),

            'estadosDisponibles' =>
                $this->estadosDisponibles(),

            'reporte' =>
                $this->construirReporte(),

            'tipoLabel' =>
                $this->tipoLabel(),

            'formatoLabel' =>
                $this->formatoLabel(),
        ];
    }

    public function generarReporte()
    {
        if (
            ! $this->periodoValido()
        ) {
            Notification::make()
                ->title(
                    'Periodo no válido'
                )
                ->body(
                    'La fecha inicial debe ser menor o igual a la fecha final.'
                )
                ->danger()
                ->send();

            return null;
        }

        $reporte =
            $this->construirReporte();

        if (
            $reporte['total'] === 0
        ) {
            Notification::make()
                ->title(
                    'Sin registros'
                )
                ->body(
                    'No existen datos que coincidan con los filtros seleccionados.'
                )
                ->warning()
                ->send();

            return null;
        }

        return match (
            $this->formato
        ) {
            'xlsx' =>
                $this->exportarExcel(
                    $reporte
                ),

            'csv' =>
                $this->exportarCsv(
                    $reporte
                ),

            default =>
                $this->exportarPdf(
                    $reporte
                ),
        };
    }

    private function construirReporte(): array
    {
        return match (
            $this->tipoReporte
        ) {
            'revisiones' =>
                $this->reporteRevisiones(),

            'propuestas' =>
                $this->reportePropuestas(),

            'financiamiento' =>
                $this->reporteFinanciamiento(),

            default =>
                $this->reporteConvocatorias(),
        };
    }

    private function reporteConvocatorias(): array
    {
        [
            $inicio,
            $fin,
        ] = $this->limites();

        $query =
            Convocatoria::query()
                ->with([
                    'categoria',
                    'organismo',
                    'fuente',
                ])
                ->withCount([
                    'requisitos',
                    'archivos',
                ])
                ->whereBetween(
                    DB::raw(
                        '
                        COALESCE(
                            fecha_extraccion,
                            created_at
                        )
                        '
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                );

        $this->aplicarFiltrosConvocatoria(
            $query
        );

        $items =
            $query
                ->orderByDesc(
                    DB::raw(
                        '
                        COALESCE(
                            fecha_extraccion,
                            created_at
                        )
                        '
                    )
                )
                ->get();

        $columnas = [
            [
                'key' => 'id',
                'label' => 'ID',
                'grupo' => 'base',
            ],
            [
                'key' => 'titulo',
                'label' => 'Convocatoria',
                'grupo' => 'general',
            ],
            [
                'key' => 'estado',
                'label' => 'Estado',
                'grupo' => 'general',
            ],
            [
                'key' => 'origen',
                'label' => 'Origen',
                'grupo' => 'general',
            ],
            [
                'key' => 'organismo',
                'label' => 'Organismo',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'categoria',
                'label' => 'Categoría',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'fuente',
                'label' => 'Fuente',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'detectada',
                'label' => 'Fecha detectada',
                'grupo' => 'fechas',
            ],
            [
                'key' => 'cierre',
                'label' => 'Fecha cierre',
                'grupo' => 'fechas',
            ],
            [
                'key' => 'monto_maximo',
                'label' => 'Monto máximo',
                'grupo' => 'montos',
            ],
            [
                'key' => 'moneda',
                'label' => 'Moneda',
                'grupo' => 'montos',
            ],
            [
                'key' => 'requisitos',
                'label' => 'Requisitos',
                'grupo' => 'requisitos',
            ],
            [
                'key' => 'documentos',
                'label' => 'Documentos',
                'grupo' => 'documentos',
            ],
        ];

        $filas =
            $items->map(
                function (
                    Convocatoria $c
                ): array {
                    $detectada =
                        $c->fecha_extraccion
                        ?? $c->created_at;

                    return [
                        'id' =>
                            $c->id,

                        'titulo' =>
                            $c->titulo,

                        'estado' =>
                            $this->estadoConvocatoriaLabel(
                                $c->estado
                            ),

                        'origen' =>
                            $c->origen
                            ?: 'No especificado',

                        'organismo' =>
                            $c->organismo?->nombre
                            ?? 'Sin organismo',

                        'categoria' =>
                            $c->categoria?->nombre
                            ?? 'Sin categoría',

                        'fuente' =>
                            $c->fuente?->nombre
                            ?? 'Sin fuente',

                        'detectada' =>
                            $detectada
                                ?->format(
                                    'd/m/Y H:i'
                                )
                            ?? 'Sin fecha',

                        'cierre' =>
                            $c->fecha_cierre
                                ?->format(
                                    'd/m/Y'
                                )
                            ?? 'Sin fecha',

                        'monto_maximo' =>
                            $c->monto_maximo !== null
                                ? number_format(
                                    (float)
                                    $c->monto_maximo,
                                    2
                                )
                                : 'No especificado',

                        'moneda' =>
                            $c->moneda
                            ?: 'MXN',

                        'requisitos' =>
                            $c->requisitos_count,

                        'documentos' =>
                            $c->archivos_count,
                    ];
                }
            );

        $montoTotal =
            $items
                ->filter(
                    fn (
                        Convocatoria $c
                    ): bool =>
                        $c->monto_maximo
                        !== null
                )
                ->sum(
                    fn (
                        Convocatoria $c
                    ): float =>
                        (float)
                        $c->monto_maximo
                );

        $metricas = [
            [
                'label' =>
                    'Convocatorias',
                'valor' =>
                    $items->count(),
            ],
            [
                'label' =>
                    'Pendientes',
                'valor' =>
                    $items
                        ->where(
                            'estado',
                            'PENDIENTE_REVISION'
                        )
                        ->count(),
            ],
            [
                'label' =>
                    'Organismos',
                'valor' =>
                    $items
                        ->pluck(
                            'organismo_id'
                        )
                        ->filter()
                        ->unique()
                        ->count(),
            ],
            [
                'label' =>
                    'Monto potencial',
                'valor' =>
                    '$'
                    . number_format(
                        $montoTotal,
                        2
                    ),
            ],
        ];

        return $this->armarResultado(
            'Reporte de Convocatorias',
            'Convocatorias registradas durante el periodo seleccionado.',
            $columnas,
            $filas,
            $metricas
        );
    }

    private function reporteRevisiones(): array
    {
        [
            $inicio,
            $fin,
        ] = $this->limites();

        $query =
            DB::table(
                'convocatoria_revisiones_administrativas AS r'
            )
                ->join(
                    'convocatorias AS c',
                    'c.id',
                    '=',
                    'r.convocatoria_id'
                )
                ->leftJoin(
                    'users AS u',
                    'u.id',
                    '=',
                    'r.revisor_id'
                )
                ->leftJoin(
                    'organismos AS o',
                    'o.id',
                    '=',
                    'c.organismo_id'
                )
                ->leftJoin(
                    'categorias AS cat',
                    'cat.id',
                    '=',
                    'c.categoria_id'
                )
                ->whereBetween(
                    'r.fecha_decision',
                    [
                        $inicio,
                        $fin,
                    ]
                );

        if (
            $this->organismo
            !== ''
        ) {
            $query->where(
                'c.organismo_id',
                (int)
                $this->organismo
            );
        }

        if (
            $this->categoria
            !== ''
        ) {
            $query->where(
                'c.categoria_id',
                (int)
                $this->categoria
            );
        }

        if (
            $this->estado
            !== ''
        ) {
            $query->where(
                'r.accion',
                $this->estado
            );
        }

        $items =
            $query
                ->select([
                    'r.id',
                    'r.convocatoria_id',
                    'r.revisor_id',
                    'r.accion',
                    'r.estado_anterior',
                    'r.estado_nuevo',
                    'r.observaciones',
                    'r.fecha_decision',
                    'c.titulo AS convocatoria',
                    'o.nombre AS organismo',
                    'cat.nombre AS categoria',
                    'u.name AS revisor_nombre',
                    'u.apellidos AS revisor_apellidos',
                ])
                ->orderByDesc(
                    'r.fecha_decision'
                )
                ->get();

        $columnas = [
            [
                'key' => 'id',
                'label' => 'ID',
                'grupo' => 'base',
            ],
            [
                'key' => 'convocatoria',
                'label' => 'Convocatoria',
                'grupo' => 'general',
            ],
            [
                'key' => 'accion',
                'label' => 'Acción',
                'grupo' => 'general',
            ],
            [
                'key' => 'estado_anterior',
                'label' => 'Estado anterior',
                'grupo' => 'general',
            ],
            [
                'key' => 'estado_nuevo',
                'label' => 'Estado nuevo',
                'grupo' => 'general',
            ],
            [
                'key' => 'revisor',
                'label' => 'Revisor',
                'grupo' => 'general',
            ],
            [
                'key' => 'organismo',
                'label' => 'Organismo',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'categoria',
                'label' => 'Categoría',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'fecha',
                'label' => 'Fecha de decisión',
                'grupo' => 'fechas',
            ],
            [
                'key' => 'observaciones',
                'label' => 'Observaciones',
                'grupo' => 'general',
            ],
        ];

        $filas =
            $items->map(
                function (
                    object $item
                ): array {
                    $revisor =
                        trim(
                            (
                                $item->revisor_nombre
                                ?? ''
                            )
                            . ' '
                            . (
                                $item->revisor_apellidos
                                ?? ''
                            )
                        );

                    return [
                        'id' =>
                            $item->id,

                        'convocatoria' =>
                            $item->convocatoria,

                        'accion' =>
                            $this->accionRevisionLabel(
                                $item->accion
                            ),

                        'estado_anterior' =>
                            $this->estadoConvocatoriaLabel(
                                $item->estado_anterior
                            ),

                        'estado_nuevo' =>
                            $this->estadoConvocatoriaLabel(
                                $item->estado_nuevo
                            ),

                        'revisor' =>
                            $revisor !== ''
                                ? $revisor
                                : 'Sin revisor',

                        'organismo' =>
                            $item->organismo
                            ?? 'Sin organismo',

                        'categoria' =>
                            $item->categoria
                            ?? 'Sin categoría',

                        'fecha' =>
                            Carbon::parse(
                                $item->fecha_decision
                            )->format(
                                'd/m/Y H:i'
                            ),

                        'observaciones' =>
                            $item->observaciones
                            ?? 'Sin observaciones',
                    ];
                }
            );

        $metricas = [
            [
                'label' =>
                    'Revisiones',
                'valor' =>
                    $items->count(),
            ],
            [
                'label' =>
                    'Aprobaciones',
                'valor' =>
                    $items
                        ->where(
                            'accion',
                            'APROBAR'
                        )
                        ->count(),
            ],
            [
                'label' =>
                    'Correcciones',
                'valor' =>
                    $items
                        ->where(
                            'accion',
                            'SOLICITAR_CORRECCIONES'
                        )
                        ->count(),
            ],
            [
                'label' =>
                    'Revisores',
                'valor' =>
                    $items
                        ->pluck(
                            'revisor_id'
                        )
                        ->filter()
                        ->unique()
                        ->count(),
            ],
        ];

        return $this->armarResultado(
            'Reporte de Revisiones',
            'Historial de decisiones administrativas registradas.',
            $columnas,
            $filas,
            $metricas
        );
    }

    private function reportePropuestas(): array
    {
        [
            $inicio,
            $fin,
        ] = $this->limites();

        $query =
            Propuesta::query()
                ->with([
                    'usuario',
                    'convocatoria.organismo',
                    'convocatoria.categoria',
                ])
                ->whereBetween(
                    'created_at',
                    [
                        $inicio,
                        $fin,
                    ]
                );

        if (
            $this->organismo
            !== ''
        ) {
            $query->whereHas(
                'convocatoria',
                fn (
                    Builder $q
                ) =>
                    $q->where(
                        'organismo_id',
                        (int)
                        $this->organismo
                    )
            );
        }

        if (
            $this->categoria
            !== ''
        ) {
            $query->whereHas(
                'convocatoria',
                fn (
                    Builder $q
                ) =>
                    $q->where(
                        'categoria_id',
                        (int)
                        $this->categoria
                    )
            );
        }

        if (
            $this->estado
            !== ''
        ) {
            $query->where(
                'estado',
                $this->estado
            );
        }

        $items =
            $query
                ->orderByDesc(
                    'created_at'
                )
                ->get();

        $columnas = [
            [
                'key' => 'id',
                'label' => 'ID',
                'grupo' => 'base',
            ],
            [
                'key' => 'titulo',
                'label' => 'Propuesta',
                'grupo' => 'general',
            ],
            [
                'key' => 'usuario',
                'label' => 'Usuario',
                'grupo' => 'general',
            ],
            [
                'key' => 'convocatoria',
                'label' => 'Convocatoria',
                'grupo' => 'general',
            ],
            [
                'key' => 'estado',
                'label' => 'Estado',
                'grupo' => 'general',
            ],
            [
                'key' => 'version',
                'label' => 'Versión',
                'grupo' => 'general',
            ],
            [
                'key' => 'organismo',
                'label' => 'Organismo',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'categoria',
                'label' => 'Categoría',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'fecha_registro',
                'label' => 'Fecha registro',
                'grupo' => 'fechas',
            ],
            [
                'key' => 'fecha_envio',
                'label' => 'Fecha envío',
                'grupo' => 'fechas',
            ],
        ];

        $filas =
            $items->map(
                function (
                    Propuesta $propuesta
                ): array {
                    $usuario =
                        trim(
                            (
                                $propuesta
                                    ->usuario
                                    ?->name
                                ?? ''
                            )
                            . ' '
                            . (
                                $propuesta
                                    ->usuario
                                    ?->apellidos
                                ?? ''
                            )
                        );

                    return [
                        'id' =>
                            $propuesta->id,

                        'titulo' =>
                            $propuesta->titulo,

                        'usuario' =>
                            $usuario !== ''
                                ? $usuario
                                : 'Sin usuario',

                        'convocatoria' =>
                            $propuesta
                                ->convocatoria
                                ?->titulo
                            ?? 'Sin convocatoria',

                        'estado' =>
                            str_replace(
                                '_',
                                ' ',
                                $propuesta->estado
                            ),

                        'version' =>
                            $propuesta->version,

                        'organismo' =>
                            $propuesta
                                ->convocatoria
                                ?->organismo
                                ?->nombre
                            ?? 'Sin organismo',

                        'categoria' =>
                            $propuesta
                                ->convocatoria
                                ?->categoria
                                ?->nombre
                            ?? 'Sin categoría',

                        'fecha_registro' =>
                            $propuesta
                                ->created_at
                                ?->format(
                                    'd/m/Y H:i'
                                )
                            ?? 'Sin fecha',

                        'fecha_envio' =>
                            $propuesta
                                ->fecha_envio
                                ?->format(
                                    'd/m/Y H:i'
                                )
                            ?? 'No enviada',
                    ];
                }
            );

        $metricas = [
            [
                'label' =>
                    'Propuestas',
                'valor' =>
                    $items->count(),
            ],
            [
                'label' =>
                    'Borradores',
                'valor' =>
                    $items
                        ->where(
                            'estado',
                            'BORRADOR'
                        )
                        ->count(),
            ],
            [
                'label' =>
                    'Enviadas',
                'valor' =>
                    $items
                        ->where(
                            'estado',
                            'ENVIADA'
                        )
                        ->count(),
            ],
            [
                'label' =>
                    'Usuarios',
                'valor' =>
                    $items
                        ->pluck(
                            'user_id'
                        )
                        ->filter()
                        ->unique()
                        ->count(),
            ],
        ];

        return $this->armarResultado(
            'Reporte de Propuestas',
            'Propuestas registradas en la plataforma durante el periodo.',
            $columnas,
            $filas,
            $metricas
        );
    }

    private function reporteFinanciamiento(): array
    {
        [
            $inicio,
            $fin,
        ] = $this->limites();

        $query =
            Convocatoria::query()
                ->with([
                    'organismo',
                    'categoria',
                ])
                ->whereBetween(
                    DB::raw(
                        '
                        COALESCE(
                            fecha_extraccion,
                            created_at
                        )
                        '
                    ),
                    [
                        $inicio,
                        $fin,
                    ]
                )
                ->where(
                    function (
                        Builder $q
                    ): void {
                        $q
                            ->whereNotNull(
                                'monto_minimo'
                            )
                            ->orWhereNotNull(
                                'monto_maximo'
                            );
                    }
                );

        $this->aplicarFiltrosConvocatoria(
            $query
        );

        $items =
            $query
                ->orderByDesc(
                    'monto_maximo'
                )
                ->get();

        $columnas = [
            [
                'key' => 'id',
                'label' => 'ID',
                'grupo' => 'base',
            ],
            [
                'key' => 'titulo',
                'label' => 'Convocatoria',
                'grupo' => 'general',
            ],
            [
                'key' => 'estado',
                'label' => 'Estado',
                'grupo' => 'general',
            ],
            [
                'key' => 'organismo',
                'label' => 'Organismo',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'categoria',
                'label' => 'Categoría',
                'grupo' => 'organismos',
            ],
            [
                'key' => 'monto_minimo',
                'label' => 'Monto mínimo',
                'grupo' => 'montos',
            ],
            [
                'key' => 'monto_maximo',
                'label' => 'Monto máximo',
                'grupo' => 'montos',
            ],
            [
                'key' => 'moneda',
                'label' => 'Moneda',
                'grupo' => 'montos',
            ],
            [
                'key' => 'modalidad',
                'label' => 'Modalidad',
                'grupo' => 'general',
            ],
            [
                'key' => 'cierre',
                'label' => 'Fecha cierre',
                'grupo' => 'fechas',
            ],
        ];

        $filas =
            $items->map(
                fn (
                    Convocatoria $c
                ): array => [
                    'id' =>
                        $c->id,

                    'titulo' =>
                        $c->titulo,

                    'estado' =>
                        $this->estadoConvocatoriaLabel(
                            $c->estado
                        ),

                    'organismo' =>
                        $c->organismo?->nombre
                        ?? 'Sin organismo',

                    'categoria' =>
                        $c->categoria?->nombre
                        ?? 'Sin categoría',

                    'monto_minimo' =>
                        $c->monto_minimo !== null
                            ? number_format(
                                (float)
                                $c->monto_minimo,
                                2
                            )
                            : 'No especificado',

                    'monto_maximo' =>
                        $c->monto_maximo !== null
                            ? number_format(
                                (float)
                                $c->monto_maximo,
                                2
                            )
                            : 'No especificado',

                    'moneda' =>
                        $c->moneda
                        ?: 'MXN',

                    'modalidad' =>
                        $c->modalidad
                        ?: 'No especificada',

                    'cierre' =>
                        $c->fecha_cierre
                            ?->format(
                                'd/m/Y'
                            )
                        ?? 'Sin fecha',
                ]
            );

        $montoTotal =
            $items->sum(
                fn (
                    Convocatoria $c
                ): float =>
                    (float)
                    (
                        $c->monto_maximo
                        ?? 0
                    )
            );

        $montoPromedio =
            $items
                ->whereNotNull(
                    'monto_maximo'
                )
                ->avg(
                    fn (
                        Convocatoria $c
                    ): float =>
                        (float)
                        $c->monto_maximo
                )
            ?? 0;

        $montoMayor =
            $items
                ->whereNotNull(
                    'monto_maximo'
                )
                ->max(
                    fn (
                        Convocatoria $c
                    ): float =>
                        (float)
                        $c->monto_maximo
                )
            ?? 0;

        $metricas = [
            [
                'label' =>
                    'Convocatorias',
                'valor' =>
                    $items->count(),
            ],
            [
                'label' =>
                    'Monto total',
                'valor' =>
                    '$'
                    . number_format(
                        $montoTotal,
                        2
                    ),
            ],
            [
                'label' =>
                    'Promedio',
                'valor' =>
                    '$'
                    . number_format(
                        $montoPromedio,
                        2
                    ),
            ],
            [
                'label' =>
                    'Mayor monto',
                'valor' =>
                    '$'
                    . number_format(
                        $montoMayor,
                        2
                    ),
            ],
        ];

        return $this->armarResultado(
            'Reporte de Financiamiento',
            'Montos registrados en las convocatorias del periodo.',
            $columnas,
            $filas,
            $metricas
        );
    }

    private function aplicarFiltrosConvocatoria(
        Builder $query
    ): void {
        if (
            $this->organismo
            !== ''
        ) {
            $query->where(
                'organismo_id',
                (int)
                $this->organismo
            );
        }

        if (
            $this->categoria
            !== ''
        ) {
            $query->where(
                'categoria_id',
                (int)
                $this->categoria
            );
        }

        if (
            $this->estado
            !== ''
        ) {
            $query->where(
                'estado',
                $this->estado
            );
        }
    }

    private function armarResultado(
        string $titulo,
        string $descripcion,
        array $columnas,
        Collection $filas,
        array $metricas
    ): array {
        $grupos = [
            'general' =>
                $this->incluirDatosGenerales,

            'organismos' =>
                $this->incluirOrganismos,

            'fechas' =>
                $this->incluirFechas,

            'montos' =>
                $this->incluirMontos,

            'requisitos' =>
                $this->incluirRequisitos,

            'documentos' =>
                $this->incluirDocumentos,
        ];

        $columnasFiltradas =
            collect(
                $columnas
            )
                ->filter(
                    fn (
                        array $columna
                    ): bool =>
                        $columna['grupo']
                        === 'base'
                        ||
                        (
                            $grupos[
                                $columna[
                                    'grupo'
                                ]
                            ]
                            ?? false
                        )
                )
                ->values()
                ->all();

        $filasFiltradas =
            $filas
                ->map(
                    function (
                        array $fila
                    ) use (
                        $columnasFiltradas
                    ): array {
                        $resultado = [];

                        foreach (
                            $columnasFiltradas
                            as $columna
                        ) {
                            $resultado[
                                $columna[
                                    'key'
                                ]
                            ] =
                                $fila[
                                    $columna[
                                        'key'
                                    ]
                                ]
                                ?? '';
                        }

                        return $resultado;
                    }
                );

        return [
            'titulo' =>
                $titulo,

            'descripcion' =>
                $descripcion,

            'columnas' =>
                $columnasFiltradas,

            'filas' =>
                $filasFiltradas,

            'metricas' =>
                $metricas,

            'total' =>
                $filasFiltradas
                    ->count(),
        ];
    }

    private function estadosDisponibles(): array
    {
        return match (
            $this->tipoReporte
        ) {
            'revisiones' => [
                'APROBAR' =>
                    'Aprobada',

                'SOLICITAR_CORRECCIONES' =>
                    'Solicitar correcciones',

                'REENVIAR_REVISION' =>
                    'Reenviada a revisión',
            ],

            'propuestas' => [
                'BORRADOR' =>
                    'Borrador',

                'GENERADA' =>
                    'Generada',

                'EDITANDO' =>
                    'Editando',

                'LISTA' =>
                    'Lista',

                'ENVIADA' =>
                    'Enviada',

                'APROBADA' =>
                    'Aprobada',

                'RECHAZADA' =>
                    'Rechazada',
            ],

            default => [
                'BORRADOR' =>
                    'Borrador',

                'PENDIENTE_REVISION' =>
                    'Pendiente de revisión',

                'REQUIERE_CORRECCIONES' =>
                    'Requiere correcciones',

                'PUBLICADA' =>
                    'Publicada',

                'ACTIVA' =>
                    'Activa',

                'CERRADA' =>
                    'Cerrada',

                'DESCARTADA' =>
                    'Descartada',

                'ARCHIVADA' =>
                    'Archivada',
            ],
        };
    }

    private function tipoLabel(): string
    {
        return match (
            $this->tipoReporte
        ) {
            'revisiones' =>
                'Revisiones',

            'propuestas' =>
                'Propuestas',

            'financiamiento' =>
                'Financiamiento',

            default =>
                'Convocatorias',
        };
    }

    private function formatoLabel(): string
    {
        return match (
            $this->formato
        ) {
            'xlsx' =>
                'Excel',

            'csv' =>
                'CSV',

            default =>
                'PDF',
        };
    }

    private function periodoValido(): bool
    {
        try {
            return Carbon::parse(
                $this->fechaInicio
            )->startOfDay()
                <=
                Carbon::parse(
                    $this->fechaFinal
                )->endOfDay();
        } catch (\Throwable) {
            return false;
        }
    }

    private function limites(): array
    {
        try {
            $inicio =
                Carbon::parse(
                    $this->fechaInicio
                )->startOfDay();

            $fin =
                Carbon::parse(
                    $this->fechaFinal
                )->endOfDay();

            return [
                $inicio,
                $fin,
            ];
        } catch (\Throwable) {
            return [
                now()->startOfYear(),
                now()->endOfYear(),
            ];
        }
    }

    private function estadoConvocatoriaLabel(
        ?string $estado
    ): string {
        return match (
            $estado
        ) {
            'BORRADOR' =>
                'Borrador',

            'PENDIENTE_REVISION' =>
                'Pendiente de revisión',

            'REQUIERE_CORRECCIONES' =>
                'Requiere correcciones',

            'PUBLICADA' =>
                'Publicada',

            'ACTIVA' =>
                'Activa',

            'CERRADA' =>
                'Cerrada',

            'DESCARTADA' =>
                'Descartada',

            'ARCHIVADA' =>
                'Archivada',

            default =>
                $estado
                ?: 'Sin estado',
        };
    }

    private function accionRevisionLabel(
        ?string $accion
    ): string {
        return match (
            $accion
        ) {
            'APROBAR' =>
                'Aprobar',

            'SOLICITAR_CORRECCIONES' =>
                'Solicitar correcciones',

            'REENVIAR_REVISION' =>
                'Reenviar a revisión',

            default =>
                $accion
                ?: 'Sin acción',
        };
    }

    private function textoUtf8(
        mixed $valor
    ): string {
        $texto =
            (string) (
                $valor
                ?? ''
            );

        if (
            mb_check_encoding(
                $texto,
                'UTF-8'
            )
        ) {
            return $texto;
        }

        return mb_convert_encoding(
            $texto,
            'UTF-8',
            'UTF-8'
        );
    }

    private function nombreArchivo(
        string $extension
    ): string {
        return sprintf(
            'reporte-%s-%s.%s',
            Str::slug(
                $this->tipoLabel()
            ),
            now()->format(
                'Ymd-His'
            ),
            $extension
        );
    }

    private function exportarPdf(
        array $reporte
    ) {
        $directorio =
            storage_path(
                'app/tmp'
            );

        if (
            ! is_dir(
                $directorio
            )
        ) {
            mkdir(
                $directorio,
                0755,
                true
            );
        }

        $archivo =
            $directorio
            . DIRECTORY_SEPARATOR
            . uniqid(
                'reporte_',
                true
            )
            . '.pdf';

        $pdf =
            Pdf::loadView(
                'pdf.directivo.reporte',
                [
                    'reporte' =>
                        $reporte,

                    'tipo' =>
                        $this->tipoLabel(),

                    'fechaInicio' =>
                        $this->fechaInicio,

                    'fechaFinal' =>
                        $this->fechaFinal,

                    'generadoEn' =>
                        now(),
                ]
            )
                ->setPaper(
                    'a4',
                    'landscape'
                );

        file_put_contents(
            $archivo,
            $pdf->output()
        );

        return response()
            ->download(
                $archivo,
                $this->nombreArchivo(
                    'pdf'
                ),
                [
                    'Content-Type' =>
                        'application/pdf',
                ]
            )
            ->deleteFileAfterSend(
                true
            );
    }

    private function exportarExcel(
        array $reporte
    ) {
        $directorio =
            storage_path(
                'app/tmp'
            );

        if (
            ! is_dir(
                $directorio
            )
        ) {
            mkdir(
                $directorio,
                0755,
                true
            );
        }

        $archivo =
            $directorio
            . DIRECTORY_SEPARATOR
            . uniqid(
                'reporte_',
                true
            )
            . '.xlsx';

        $writer =
            new Writer();

        $writer->openToFile(
            $archivo
        );

        $writer->addRow(
            Row::fromValues(
                collect(
                    $reporte[
                        'columnas'
                    ]
                )
                    ->pluck(
                        'label'
                    )
                    ->all()
            )
        );

        foreach (
            $reporte['filas']
            as $fila
        ) {
            $writer->addRow(
                Row::fromValues(
                    array_map(
                        fn (
                            mixed $valor
                        ): string =>
                            $this->textoUtf8(
                                $valor
                            ),
                        array_values(
                            $fila
                        )
                    )
                )
            );
        }

        $writer->close();

        return response()
            ->download(
                $archivo,
                $this->nombreArchivo(
                    'xlsx'
                )
            )
            ->deleteFileAfterSend(
                true
            );
    }

    private function exportarCsv(
        array $reporte
    ) {
        $directorio =
            storage_path(
                'app/tmp'
            );

        if (
            ! is_dir(
                $directorio
            )
        ) {
            mkdir(
                $directorio,
                0755,
                true
            );
        }

        $archivo =
            $directorio
            . DIRECTORY_SEPARATOR
            . uniqid(
                'reporte_',
                true
            )
            . '.csv';

        $salida =
            fopen(
                $archivo,
                'wb'
            );

        fwrite(
            $salida,
            "\xEF\xBB\xBF"
        );

        fputcsv(
            $salida,
            collect(
                $reporte[
                    'columnas'
                ]
            )
                ->pluck(
                    'label'
                )
                ->all()
        );

        foreach (
            $reporte['filas']
            as $fila
        ) {
            fputcsv(
                $salida,
                array_map(
                    fn (
                        mixed $valor
                    ): string =>
                        $this->textoUtf8(
                            $valor
                        ),
                    array_values(
                        $fila
                    )
                )
            );
        }

        fclose(
            $salida
        );

        return response()
            ->download(
                $archivo,
                $this->nombreArchivo(
                    'csv'
                ),
                [
                    'Content-Type' =>
                        'text/csv; charset=UTF-8',
                ]
            )
            ->deleteFileAfterSend(
                true
            );
    }

}
