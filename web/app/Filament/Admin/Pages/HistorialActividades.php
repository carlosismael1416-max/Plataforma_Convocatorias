<?php

namespace App\Filament\Admin\Pages;

use App\Models\Actividad;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Historial de actividades de todos los usuarios (tabla `actividades`):
 * inicios de sesión, cambios de perfil y contraseña, propuestas, dictámenes,
 * convocatorias, usuarios, roles, fuentes, motor, reportes y bitácora.
 */
class HistorialActividades extends Page
{
    use WithPagination;

    protected string $view =
        'filament.admin.pages.historial-actividades';

    // La vista ya trae su encabezado.
    protected ?string $heading = '';

    protected static ?string $navigationLabel =
        'Historial de actividades';

    protected static ?string $slug =
        'historial-actividades';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    // Al final del menú, después de Convocatorias.
    protected static ?int $navigationSort =
        9;

    #[Url(as: 'q', except: '')]
    public string $buscar = '';

    #[Url(as: 'usuario', except: '')]
    public string $usuario = '';

    #[Url(as: 'accion', except: '')]
    public string $accion = '';

    #[Url(as: 'periodo', except: '')]
    public string $periodo = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('ADMINISTRADOR');
    }

    public function getTitle(): string
    {
        return 'Historial de actividades';
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'usuario', 'accion', 'periodo'], true)) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->usuario = '';
        $this->accion = '';
        $this->periodo = '';

        $this->resetPage();
    }

    /** Inicio del periodo en UTC (las fechas se guardan en UTC; el día es el de Mérida). */
    public static function inicioPeriodo(string $periodo): ?Carbon
    {
        $hoy = now('America/Merida')->startOfDay();

        return match ($periodo) {
            'hoy' => $hoy->utc(),
            'semana' => $hoy->subDays(6)->utc(),
            'mes' => $hoy->subDays(29)->utc(),
            default => null,
        };
    }

    protected function getViewData(): array
    {
        $query = Actividad::query()->with('usuario:id,name,apellidos,email');

        $buscar = trim($this->buscar);

        if ($buscar !== '') {
            $patron = '%' . $buscar . '%';
            $query->where(fn ($q) => $q
                ->where('descripcion', 'ilike', $patron)
                ->orWhereHas('usuario', fn ($u) => $u
                    ->where('name', 'ilike', $patron)
                    ->orWhere('apellidos', 'ilike', $patron)
                    ->orWhere('email', 'ilike', $patron)));
        }

        if ($this->usuario !== '') {
            $query->where('user_id', (int) $this->usuario);
        }

        if ($this->accion !== '') {
            $query->where('accion', $this->accion);
        }

        if ($desde = self::inicioPeriodo($this->periodo)) {
            $query->where('updated_at', '>=', $desde);
        }

        $base = fn () => Actividad::query();

        return [
            'tarjetas' => [
                ['hoy', 'Hoy', $base()->where('updated_at', '>=', self::inicioPeriodo('hoy'))->count(), 'Acciones de hoy', 'reloj', 'yellow'],
                ['semana', 'Últimos 7 días', $base()->where('updated_at', '>=', self::inicioPeriodo('semana'))->count(), 'Acciones de la semana', 'calendario', 'blue'],
                ['mes', 'Últimos 30 días', $base()->where('updated_at', '>=', self::inicioPeriodo('mes'))->count(), 'Acciones del mes', 'tendencia', 'green'],
                ['', 'Todas', $base()->count(), 'Historial completo', 'documento', 'violet'],
            ],
            'usuarios' => User::query()
                ->whereIn('id', Actividad::query()->whereNotNull('user_id')->distinct()->select('user_id'))
                ->orderBy('name')
                ->get(['id', 'name', 'apellidos']),
            'accionesUsadas' => $base()->distinct()->orderBy('accion')->pluck('accion'),
            'actividades' => $query->orderByDesc('updated_at')->orderByDesc('id')->paginate(25),
        ];
    }
}
