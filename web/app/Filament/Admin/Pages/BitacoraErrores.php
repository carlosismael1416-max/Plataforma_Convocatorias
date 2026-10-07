<?php

namespace App\Filament\Admin\Pages;

use App\Models\BitacoraError;
use App\Models\EjecucionScraping;
use App\Models\Fuente;
use App\Models\User;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class BitacoraErrores extends Page
{
    use WithPagination;

    protected string $view =
        'filament.admin.pages.bitacora-errores';

    protected static ?string $navigationLabel =
        'Bitácora de Errores';

    protected static ?string $slug =
        'bitacora-errores';

    protected static ?int $navigationSort =
        6;

    #[Url(as: 'q', except: '')]
    public string $buscar = '';

    #[Url(as: 'tipo', except: '')]
    public string $tipo = '';

    #[Url(as: 'fuente', except: '')]
    public string $fuente = '';

    #[Url(as: 'estado', except: '')]
    public string $estado = '';

    #[Url(as: 'ejecucion', except: null)]
    public ?int $ejecucion = null;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    public function getTitle(): string
    {
        return 'Bitácora de Errores';
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFuente(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function aplicarFiltros(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->tipo = '';
        $this->fuente = '';
        $this->estado = '';
        $this->ejecucion = null;

        $this->resetPage();
    }

    public function quitarFiltroEjecucion(): void
    {
        $this->ejecucion = null;

        $this->resetPage();
    }

    protected function getViewData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | INDICADORES GENERALES
        |--------------------------------------------------------------------------
        */

        $totalErrores =
            BitacoraError::query()
                ->count();

        $pendientes =
            BitacoraError::query()
                ->where(
                    'resuelto',
                    false
                )
                ->count();

        $resueltos =
            BitacoraError::query()
                ->where(
                    'resuelto',
                    true
                )
                ->count();

        $fuentesRegistradas =
            Fuente::query()
                ->count();

        /*
        |--------------------------------------------------------------------------
        | FUENTES MÁS AFECTADAS
        |--------------------------------------------------------------------------
        */

        $topFuentes =
            BitacoraError::query()
                ->select('fuente_id')
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->whereNotNull(
                    'fuente_id'
                )
                ->groupBy(
                    'fuente_id'
                )
                ->orderByDesc(
                    'total'
                )
                ->limit(3)
                ->with('fuente')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | OPCIONES DE FILTRO
        |--------------------------------------------------------------------------
        */

        $tiposError =
            BitacoraError::query()
                ->whereNotNull(
                    'tipo_error'
                )
                ->where(
                    'tipo_error',
                    '<>',
                    ''
                )
                ->distinct()
                ->orderBy(
                    'tipo_error'
                )
                ->pluck(
                    'tipo_error',
                    'tipo_error'
                );

        $fuentes =
            Fuente::query()
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id',
                    'nombre',
                ]);

        /*
        |--------------------------------------------------------------------------
        | CONSULTA FILTRADA
        |--------------------------------------------------------------------------
        */

        $query =
            BitacoraError::query()
                ->with([
                    'fuente',
                    'ejecucionScraping',
                ]);

        $buscar =
            trim(
                $this->buscar
            );

        if ($buscar !== '') {
            $query->where(
                function ($consulta) use (
                    $buscar
                ) {
                    $patron =
                        '%'.$buscar.'%';

                    $consulta
                        ->where(
                            'mensaje',
                            'ilike',
                            $patron
                        )
                        ->orWhere(
                            'tipo_error',
                            'ilike',
                            $patron
                        )
                        ->orWhere(
                            'codigo_error',
                            'ilike',
                            $patron
                        )
                        ->orWhere(
                            'detalle',
                            'ilike',
                            $patron
                        );
                }
            );
        }

        if ($this->tipo !== '') {
            $query->where(
                'tipo_error',
                $this->tipo
            );
        }

        if ($this->fuente !== '') {
            $query->where(
                'fuente_id',
                (int) $this->fuente
            );
        }

        if ($this->estado === 'pendiente') {
            $query->where(
                'resuelto',
                false
            );
        }

        if ($this->estado === 'resuelto') {
            $query->where(
                'resuelto',
                true
            );
        }

        if ($this->ejecucion !== null) {
            $query->where(
                'ejecucion_scraping_id',
                $this->ejecucion
            );
        }

        $errores =
            $query
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->paginate(20);

        $ejecucionContexto =
            $this->ejecucion !== null
                ? EjecucionScraping::query()
                    ->find(
                        $this->ejecucion
                    )
                : null;

        return [
            'totalErrores' =>
                $totalErrores,

            'pendientes' =>
                $pendientes,

            'resueltos' =>
                $resueltos,

            'fuentesRegistradas' =>
                $fuentesRegistradas,

            'topFuentes' =>
                $topFuentes,

            'tiposError' =>
                $tiposError,

            'fuentes' =>
                $fuentes,

            'errores' =>
                $errores,

            'ejecucionContexto' =>
                $ejecucionContexto,
        ];
    }
}
