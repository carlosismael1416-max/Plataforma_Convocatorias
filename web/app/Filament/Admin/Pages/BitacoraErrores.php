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

    // La vista ya trae su encabezado.
    protected ?string $heading = '';

    protected static ?string $navigationLabel =
        'Bitácora de Errores';

    protected static ?string $slug =
        'bitacora-errores';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?int $navigationSort =
        7;

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

    /** De dónde viene cada tipo de incidencia (etiqueta, icono, color). */
    public const TIPOS = [
        'BUSCADOR' => ['Buscador', '🔎', 'motor'],
        'PUBLICACION' => ['Publicación', '📤', 'motor'],
        'FUENTE' => ['Fuente oficial', '🌐', 'motor'],
        'SEGUIMIENTO' => ['Seguimiento', '🔁', 'motor'],
        'REVALIDACION' => ['Revalidación', '✔️', 'motor'],
        'MANTENIMIENTO' => ['Mantenimiento', '🧹', 'motor'],
        'CICLO_FALLIDO' => ['Ciclo fallido', '⛔', 'motor'],
        'API' => ['API del motor', '🔌', 'api'],
        'WEB' => ['Página web', '🖥️', 'web'],
    ];

    public static function infoTipo(?string $tipo): array
    {
        return self::TIPOS[$tipo] ?? [ucfirst(mb_strtolower(str_replace('_', ' ', (string) ($tipo ?: 'Otro')))), '⚠️', 'motor'];
    }

    protected function getViewData(): array
    {
        $base = fn () => BitacoraError::query();

        $query = BitacoraError::query()->with(['fuente', 'ejecucionScraping']);

        $buscar = trim($this->buscar);

        if ($buscar !== '') {
            $patron = '%' . $buscar . '%';
            $query->where(fn ($q) => $q
                ->where('mensaje', 'ilike', $patron)
                ->orWhere('tipo_error', 'ilike', $patron)
                ->orWhere('codigo_error', 'ilike', $patron)
                ->orWhere('detalle', 'ilike', $patron)
                ->orWhere('url', 'ilike', $patron));
        }

        if ($this->tipo !== '') {
            $query->where('tipo_error', $this->tipo);
        }

        if ($this->fuente !== '') {
            $query->where('fuente_id', (int) $this->fuente);
        }

        match ($this->estado) {
            'pendiente' => $query->where('resuelto', false),
            'resuelto' => $query->where('resuelto', true),
            'hoy' => $query->where('updated_at', '>=', today()),
            default => null,
        };

        if ($this->ejecucion !== null) {
            $query->where('ejecucion_scraping_id', $this->ejecucion);
        }

        return [
            'tarjetas' => [
                ['pendiente', 'Pendientes', $base()->where('resuelto', false)->count(), 'Sin resolver', 'alerta', 'red'],
                ['hoy', 'Hoy', $base()->where('updated_at', '>=', today())->count(), 'Ocurridas o repetidas hoy', 'reloj', 'yellow'],
                ['resuelto', 'Resueltas', $base()->where('resuelto', true)->count(), 'Atendidas', 'aprobado', 'green'],
                ['', 'Todas', $base()->count(), 'Historial completo', 'documento', 'blue'],
            ],
            'tiposUsados' => $base()->whereNotNull('tipo_error')->distinct()->orderBy('tipo_error')->pluck('tipo_error'),
            'fuentes' => Fuente::query()->orderBy('nombre')->get(['id', 'nombre']),
            'errores' => $query->orderByDesc('updated_at')->orderByDesc('id')->paginate(20),
            'ejecucionContexto' => $this->ejecucion !== null ? EjecucionScraping::query()->find($this->ejecucion) : null,
        ];
    }
}
