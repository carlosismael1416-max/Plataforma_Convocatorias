<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\PropuestaRevision;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

/**
 * Propuestas enviadas a revisión por docentes y directivos (incluidas las del
 * propio directivo) y los últimos dictámenes.
 */
class PropuestasPorRevisar extends Page
{
    use WithPagination;

    /** Movimientos del historial: etiqueta y clase visual. */
    public const ACCIONES = [
        'ENVIAR' => ['Enviada a revisión', 'enviada'],
        'APROBAR' => ['Aprobada', 'aprobada'],
        'DEVOLVER' => ['Devuelta con observaciones', 'devuelta'],
        'RECHAZAR' => ['Rechazada', 'rechazada'],
    ];

    // 'pendientes' o 'historial' (pestañas).
    public string $seccion = 'pendientes';

    public string $convocatoria = '';

    public string $accion = '';

    public string $buscar = '';

    public function mount(): void
    {
        if (request()->query('seccion') === 'historial') {
            $this->seccion = 'historial';
        }
    }

    public function cambiarSeccion(string $seccion): void
    {
        $this->seccion = $seccion === 'historial' ? 'historial' : 'pendientes';
        $this->resetPage();
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['convocatoria', 'accion', 'buscar'], true)) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['convocatoria', 'accion', 'buscar']);
        $this->resetPage();
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $navigationLabel = 'Propuestas por revisar';

    protected static ?string $title = 'Propuestas por revisar';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.directivo.pages.propuestas-por-revisar';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DIRECTIVO');
    }

    public static function getNavigationBadge(): ?string
    {
        $pendientes = Propuesta::query()->where('estado', 'ENVIADA')->count();

        return $pendientes > 0 ? (string) $pendientes : null;
    }

    protected function getViewData(): array
    {
        $fila = fn (Propuesta $p): array => [
            'id' => $p->id,
            'titulo' => $p->titulo,
            'autor' => trim(($p->usuario?->name ?? '') . ' ' . ($p->usuario?->apellidos ?? '')),
            'rol' => $p->usuario?->role?->nombre === 'DIRECTIVO' ? 'Directivo' : 'Docente',
            'propia' => $p->user_id === auth()->id(),
            'convocatoria' => $p->convocatoria?->titulo,
            'estado' => $p->estado,
            'fecha' => ($p->fecha_envio ?? $p->updated_at)?->locale('es')->isoFormat('D MMM YYYY'),
            'url' => RevisarPropuesta::getUrl(['propuesta' => $p->id]),
        ];

        $base = Propuesta::query()->with(['usuario.role', 'convocatoria:id,titulo']);

        // Historial: cada envío y dictamen, de todas las propuestas.
        $movimientos = PropuestaRevision::query()
            ->with(['usuario:id,name,apellidos', 'propuesta.usuario:id,name,apellidos', 'propuesta.convocatoria:id,titulo'])
            ->whereHas('propuesta')
            ->when(ctype_digit($this->convocatoria), fn (Builder $q) => $q->whereHas('propuesta', fn (Builder $p) => $p->where('convocatoria_id', (int) $this->convocatoria)))
            ->when(isset(self::ACCIONES[$this->accion]), fn (Builder $q) => $q->where('accion', $this->accion))
            ->when(trim($this->buscar) !== '', function (Builder $q): void {
                $patron = '%' . trim($this->buscar) . '%';
                $q->where(fn (Builder $w) => $w
                    ->where('observaciones', 'ilike', $patron)
                    ->orWhereHas('propuesta', fn (Builder $p) => $p->where('titulo', 'ilike', $patron)
                        ->orWhereHas('usuario', fn (Builder $u) => $u->where('name', 'ilike', $patron)->orWhere('apellidos', 'ilike', $patron))));
            })
            ->latest()
            ->latest('id');

        $conteo = PropuestaRevision::query()->selectRaw('accion, count(*) as n')->groupBy('accion')->pluck('n', 'accion');

        return [
            'movimientos' => $movimientos->paginate(15),
            'conteoAcciones' => $conteo,
            'convocatoriasConMovimientos' => Convocatoria::query()
                ->whereIn('id', Propuesta::query()->whereHas('revisiones')->select('convocatoria_id'))
                ->orderBy('titulo')
                ->get(['id', 'titulo']),
            'nombre' => fn ($u): string => trim(($u?->name ?? '') . ' ' . ($u?->apellidos ?? '')) ?: 'Usuario',

            'pendientes' => (clone $base)
                ->where('estado', 'ENVIADA')
                ->orderBy('fecha_envio')
                ->get()
                ->map($fila)
                ->all(),

            'dictaminadas' => (clone $base)
                ->whereIn('estado', ['APROBADA', 'RECHAZADA'])
                ->latest('updated_at')
                ->limit(10)
                ->get()
                ->map($fila)
                ->all(),
        ];
    }
}
