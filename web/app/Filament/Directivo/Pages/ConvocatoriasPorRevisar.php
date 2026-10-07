<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\Organismo;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class ConvocatoriasPorRevisar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Convocatorias por revisar';

    protected static ?int $navigationSort = 4;


    use WithPagination;

    protected string $view =
        'filament.directivo.pages.convocatorias-por-revisar';

    protected static ?string $slug =
        'convocatorias-por-revisar';

    public string $buscar = '';

    public string $organismo = '';

    public string $orden = 'recientes';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'DIRECTIVO'
            );
    }

    public function getTitle(): string
    {
        return 'Convocatorias por Revisar';
    }

    public function aplicarBusqueda(): void
    {
        $this->buscar = trim(
            $this->buscar
        );

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->organismo = '';
        $this->orden = 'recientes';

        $this->resetPage();
    }

    public function updatedOrganismo(): void
    {
        $this->resetPage();
    }

    public function updatedOrden(): void
    {
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Organismos presentes entre las pendientes
        |--------------------------------------------------------------------------
        */

        $organismoIds =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->whereNotNull(
                    'organismo_id'
                )
                ->select(
                    'organismo_id'
                );

        $organismos =
            Organismo::query()
                ->whereIn(
                    'id',
                    $organismoIds
                )
                ->orderBy('nombre')
                ->get([
                    'id',
                    'nombre',
                ]);

        /*
        |--------------------------------------------------------------------------
        | Consulta principal
        |--------------------------------------------------------------------------
        */

        $query =
            Convocatoria::query()
                ->with([
                    'organismo',
                    'categoria',
                    'fuente',
                ])
                ->withCount(
                    'archivos'
                )
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                );

        if (
            trim(
                $this->buscar
            ) !== ''
        ) {
            $buscar =
                trim(
                    $this->buscar
                );

            $query->where(
                function (
                    Builder $q
                ) use ($buscar): void {
                    $q
                        ->where(
                            'titulo',
                            'ilike',
                            '%' . $buscar . '%'
                        )
                        ->orWhere(
                            'descripcion',
                            'ilike',
                            '%' . $buscar . '%'
                        )
                        ->orWhere(
                            'objetivo',
                            'ilike',
                            '%' . $buscar . '%'
                        )
                        ->orWhereHas(
                            'organismo',
                            fn (
                                Builder $organismo
                            ) =>
                                $organismo->where(
                                    'nombre',
                                    'ilike',
                                    '%' . $buscar . '%'
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
                                    '%' . $buscar . '%'
                                )
                        )
                        ->orWhereHas(
                            'fuente',
                            fn (
                                Builder $fuente
                            ) =>
                                $fuente->where(
                                    'nombre',
                                    'ilike',
                                    '%' . $buscar . '%'
                                )
                        );
                }
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

        /*
        |--------------------------------------------------------------------------
        | Ordenamiento
        |--------------------------------------------------------------------------
        */

        match ($this->orden) {
            'cierre' =>
                $query
                    ->orderByRaw(
                        "
                        CASE
                            WHEN fecha_cierre >= CURRENT_DATE
                                THEN 0
                            WHEN fecha_cierre < CURRENT_DATE
                                THEN 1
                            ELSE 2
                        END
                        "
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
                    ->orderByRaw(
                        '
                        COALESCE(
                            fecha_extraccion,
                            created_at
                        ) DESC
                        '
                    )
                    ->orderByDesc('id'),
        };

        $convocatorias =
            $query->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | Indicadores reales
        |--------------------------------------------------------------------------
        */

        $pendientes =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->count();

        $cierreProximo =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->whereNotNull(
                    'fecha_cierre'
                )
                ->whereDate(
                    'fecha_cierre',
                    '>=',
                    now()->toDateString()
                )
                ->whereDate(
                    'fecha_cierre',
                    '<=',
                    now()
                        ->addDays(30)
                        ->toDateString()
                )
                ->count();

        $automaticas =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->where(
                    'origen',
                    'SCRAPING'
                )
                ->count();

        $manuales =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->where(
                    'origen',
                    'MANUAL'
                )
                ->count();

        return [
            'convocatorias' =>
                $convocatorias,

            'organismos' =>
                $organismos,

            'pendientes' =>
                $pendientes,

            'cierreProximo' =>
                $cierreProximo,

            'automaticas' =>
                $automaticas,

            'manuales' =>
                $manuales,
        ];
    }
}
