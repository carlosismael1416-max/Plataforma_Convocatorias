<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Concerns\GestionaMisConvocatorias;
use App\Models\Categoria;
use App\Models\Convocatoria;
use App\Models\Organismo;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class BuscarConvocatorias extends Page
{
    /** Estados visibles para todos (lo demás es interno del motor). */
    public const ESTADOS_VISIBLES = ['PUBLICADA', 'ACTIVA', 'CERRADA'];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Buscar convocatorias';

    protected static ?int $navigationSort = 4;


    use GestionaMisConvocatorias;

    use WithPagination;

    protected static ?string $title =
        'Buscar Convocatorias';

    protected static ?string $slug =
        'buscar-convocatorias';

    protected string $view =
        'filament.directivo.pages.buscar-convocatorias';

    // La vista ya trae su encabezado.
    protected ?string $heading = '';

    public string $buscar = '';

    public string $categoria = '';

    public string $organismo = '';

    public string $estado = '';

    public string $fechaCierre = '';

    public string $orden = 'recientes';


    public function mount(): void
    {
        // Búsqueda enviada desde el header del panel mediante ?q=
        $busquedaInicial = request()->query('q');

        if (is_string($busquedaInicial)) {
            $this->buscar = trim($busquedaInicial);
        }
    }

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'DIRECTIVO'
            );
    }

    public function aplicarBusqueda(): void
    {
        $this->buscar = trim(
            $this->buscar
        );

        $this->resetPage();
    }

    public function updatedCategoria(): void
    {
        $this->resetPage();
    }

    public function updatedOrganismo(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFechaCierre(): void
    {
        $this->resetPage();
    }

    public function updatedOrden(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->categoria = '';
        $this->organismo = '';
        $this->estado = '';
        $this->fechaCierre = '';
        $this->orden = 'recientes';

        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $categorias =
            Categoria::query()
                ->orderBy('nombre')
                ->get([
                    'id',
                    'nombre',
                ]);

        $organismos =
            Organismo::query()
                ->orderBy('nombre')
                ->get([
                    'id',
                    'nombre',
                ]);

        // Por defecto, lo mismo que ve el docente (publicadas y vigentes); el
        // historial de cerradas solo con su filtro. Lo que el validador no
        // aprobó es interno del motor y no se muestra.
        $query =
            Convocatoria::query()
                ->when(
                    $this->estado === 'HISTORIAL',
                    fn ($q) => $q->historial(),
                    fn ($q) => $q->when(
                        $this->estado === 'TODAS',
                        fn ($t) => $t->where(fn ($w) => $w->visibles()->orWhere(fn ($h) => $h->historial())),
                        fn ($v) => $v->visibles(),
                    ),
                )
                ->with([
                    'categoria',
                    'organismo',
                    'fuente',
                ]);

        /*
        |--------------------------------------------------------------------------
        | Búsqueda libre PostgreSQL
        |--------------------------------------------------------------------------
        */

        if (
            trim(
                $this->buscar
            ) !== ''
        ) {
            $texto =
                trim(
                    $this->buscar
                );

            $query->where(
                function (
                    Builder $q
                ) use ($texto): void {
                    $q
                        ->where(
                            'titulo',
                            'ilike',
                            '%' . $texto . '%'
                        )
                        ->orWhere(
                            'descripcion',
                            'ilike',
                            '%' . $texto . '%'
                        )
                        ->orWhere(
                            'objetivo',
                            'ilike',
                            '%' . $texto . '%'
                        )
                        ->orWhere(
                            'modalidad',
                            'ilike',
                            '%' . $texto . '%'
                        )
                        ->orWhere(
                            'ubicacion',
                            'ilike',
                            '%' . $texto . '%'
                        )
                        ->orWhereHas(
                            'organismo',
                            fn (
                                Builder $organismo
                            ) =>
                                $organismo->where(
                                    'nombre',
                                    'ilike',
                                    '%' . $texto . '%'
                                )
                        )
                        ->orWhereHas(
                            'categoria',
                            fn (
                                Builder $categoria
                            ) =>
                                $categoria->where(
                                    'nombre',
                                    'ilike',
                                    '%' . $texto . '%'
                                )
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */

        if (
            $this->categoria
            !== ''
        ) {
            $query->where(
                'categoria_id',
                (int) $this->categoria
            );
        }

        if (
            $this->organismo
            !== ''
        ) {
            $query->where(
                'organismo_id',
                (int) $this->organismo
            );
        }


        if (
            $this->fechaCierre
            !== ''
        ) {
            $query->whereDate(
                'fecha_cierre',
                $this->fechaCierre
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ordenamiento
        |--------------------------------------------------------------------------
        */

        match ($this->orden) {
            'cierre' =>
                $query
                    ->orderByRaw(
                        'fecha_cierre IS NULL'
                    )
                    ->orderBy(
                        'fecha_cierre'
                    )
                    ->orderByDesc('id'),

            'monto' =>
                $query
                    ->orderByRaw(
                        'monto_maximo IS NULL'
                    )
                    ->orderByDesc(
                        'monto_maximo'
                    )
                    ->orderByDesc('id'),

            default =>
                $query
                    ->orderByDesc(
                        'created_at'
                    )
                    ->orderByDesc('id'),
        };

        $convocatorias =
            $query->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | Resumen general
        |--------------------------------------------------------------------------
        |
        | Son métricas reales de toda la tabla.
        | No dependen del filtro actual.
        |--------------------------------------------------------------------------
        */

        $abiertas =
            Convocatoria::query()
                ->visibles()
                ->count();

        $enRevision =
            Convocatoria::query()
                ->historial()
                ->count();

        $total = $abiertas + $enRevision;

        $proximasCerrar =
            Convocatoria::query()
                ->visibles()
                ->whereNotNull('fecha_cierre')
                ->whereDate('fecha_cierre', '<=', now()->addDays(30)->toDateString())
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Categorías con registros reales
        |--------------------------------------------------------------------------
        */

        $categoriasResumen =
            DB::table(
                'convocatorias'
            )
                ->whereIn('convocatorias.estado', ['PUBLICADA', 'ACTIVA'])
                ->where(fn ($q) => $q->whereNull('convocatorias.fecha_cierre')->orWhereDate('convocatorias.fecha_cierre', '>=', today()))
                ->join(
                    'categorias',
                    'categorias.id',
                    '=',
                    'convocatorias.categoria_id'
                )
                ->select(
                    'categorias.id',
                    'categorias.nombre'
                )
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->groupBy(
                    'categorias.id',
                    'categorias.nombre'
                )
                ->orderByDesc('total')
                ->orderBy(
                    'categorias.nombre'
                )
                ->limit(6)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Chips de filtros activos
        |--------------------------------------------------------------------------
        */

        $filtrosActivos = [];

        if (
            trim(
                $this->buscar
            ) !== ''
        ) {
            $filtrosActivos[] =
                'Texto: '
                . trim(
                    $this->buscar
                );
        }

        if (
            $this->categoria
            !== ''
        ) {
            $nombre =
                $categorias
                    ->firstWhere(
                        'id',
                        (int)
                        $this->categoria
                    )
                    ?->nombre;

            if ($nombre) {
                $filtrosActivos[] =
                    'Área: '
                    . $nombre;
            }
        }

        if (
            $this->organismo
            !== ''
        ) {
            $nombre =
                $organismos
                    ->firstWhere(
                        'id',
                        (int)
                        $this->organismo
                    )
                    ?->nombre;

            if ($nombre) {
                $filtrosActivos[] =
                    'Organismo: '
                    . $nombre;
            }
        }

        if (
            $this->estado
            !== ''
        ) {
            $filtrosActivos[] =
                'Estado: '
                . $this->etiquetaEstado(
                    $this->estado
                );
        }

        if (
            $this->fechaCierre
            !== ''
        ) {
            $filtrosActivos[] =
                'Cierre: '
                . $this->fechaCierre;
        }

        $mias = $this->idsDeMisConvocatorias();

        return [
            'guardadasIds' => $mias['guardadas'],

            'favoritasIds' => $mias['favoritas'],

            'convocatorias' =>
                $convocatorias,

            'categorias' =>
                $categorias,

            'organismos' =>
                $organismos,

            'categoriasResumen' =>
                $categoriasResumen,

            'filtrosActivos' =>
                $filtrosActivos,

            'total' =>
                $total,

            'abiertas' =>
                $abiertas,

            'enRevision' =>
                $enRevision,

            'proximasCerrar' =>
                $proximasCerrar,
        ];
    }

    private function etiquetaEstado(
        string $estado
    ): string {
        return match ($estado) {
            'HISTORIAL' =>
                'Cerradas (historial)',

            'TODAS' =>
                'Vigentes y cerradas',

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
                $estado,
        };
    }
}
