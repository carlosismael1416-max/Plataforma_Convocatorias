<?php

namespace App\Filament\Docente\Pages;

use App\Filament\Concerns\RolDelPanel;
use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use App\Models\Organismo;
use App\Models\Propuesta;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

/**
 * Convocatorias que ya cerraron (`Convocatoria::historial()`), solo de
 * consulta, para que todos vean qué se ha publicado antes. La misma página
 * se registra en los paneles docente, directivo y admin.
 */
class HistorialConvocatorias extends Page
{
    use RolDelPanel;

    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Historial';

    protected static ?string $title = 'Historial de convocatorias';

    protected static ?string $slug = 'historial-convocatorias';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.docente.pages.historial-convocatorias';

    protected ?string $heading = '';

    public string $buscar = '';

    public string $organismo = '';

    public string $anio = '';

    public string $orden = 'recientes';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(static::rolDelPanel());
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'organismo', 'anio', 'orden'], true)) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['buscar', 'organismo', 'anio', 'orden']);
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        $query = Convocatoria::query()
            ->historial()
            ->with(['organismo', 'categoria']);

        $buscar = trim($this->buscar);

        if ($buscar !== '') {
            $patron = '%' . $buscar . '%';
            $query->where(
                fn (Builder $q) => $q
                    ->where('titulo', 'ilike', $patron)
                    ->orWhere('descripcion', 'ilike', $patron)
                    ->orWhereHas('organismo', fn (Builder $o) => $o->where('nombre', 'ilike', $patron))
            );
        }

        if (ctype_digit($this->organismo)) {
            $query->where('organismo_id', (int) $this->organismo);
        }

        if (preg_match('/^\d{4}$/', $this->anio)) {
            $query->whereYear('fecha_cierre', (int) $this->anio);
        }

        match ($this->orden) {
            'antiguas' => $query->orderByRaw('fecha_cierre asc nulls last'),
            'titulo' => $query->orderBy('titulo'),
            default => $query->orderByRaw('fecha_cierre desc nulls last')->orderByDesc('id'),
        };

        $anios = Convocatoria::query()
            ->historial()
            ->whereNotNull('fecha_cierre')
            ->selectRaw('distinct extract(year from fecha_cierre)::int as anio')
            ->orderByDesc('anio')
            ->pluck('anio')
            ->all();

        $organismos = Organismo::query()
            ->whereIn('id', Convocatoria::query()->historial()->whereNotNull('organismo_id')->select('organismo_id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $panel = filament()->getId();

        return [
            'convocatorias' => $query->paginate(12),
            'total' => Convocatoria::query()->historial()->count(),
            'anios' => $anios,
            'organismos' => $organismos,
            'conPropuesta' => Propuesta::query()
                ->where('user_id', auth()->id())
                ->pluck('convocatoria_id')
                ->map(fn ($id): int => (int) $id)
                ->all(),
            'urlDetalle' => fn (Convocatoria $c): string => $panel === 'admin'
                ? ConvocatoriaResource::getUrl('view', ['record' => $c->id])
                : route('filament.' . $panel . '.pages.detalle-convocatoria', ['record' => $c->id]),
        ];
    }
}
