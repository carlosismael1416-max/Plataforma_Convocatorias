<?php

namespace App\Filament\Docente\Pages;

use App\Models\Categoria;
use App\Models\Convocatoria;
use App\Models\Organismo;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class BuscarConvocatorias extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel =
        'Buscar convocatorias';

    protected static ?string $title =
        'Buscar convocatorias';

    protected static ?int $navigationSort = 1;

    protected string $view =
        'filament.docente.pages.buscar-convocatorias';

    public string $buscar = '';

    public string $categoria = '';

    public string $organismo = '';

    public string $estado = '';

    public string $cierre = '';

    public string $orden = 'cierre';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function mount(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Búsqueda enviada desde el Dashboard mediante ?q=
        |--------------------------------------------------------------------------
        */

        $busquedaInicial = request()->query('q');

        if (is_string($busquedaInicial)) {
            $this->buscar = trim($busquedaInicial);
        }
    }

    public function updatedBuscar(): void
    {
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

    public function updatedCierre(): void
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
        $this->cierre = '';
        $this->orden = 'cierre';

        $this->resetPage();
    }

    public function guardarConvocatoria(
        int $convocatoriaId
    ): void {
        $usuario = auth()->user();

        abort_unless(
            $usuario instanceof User
                && $usuario->tieneRol('DOCENTE'),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | Un Docente solamente puede guardar convocatorias que realmente
        | están disponibles para él.
        |
        */

        $convocatoria = Convocatoria::query()
            ->whereKey($convocatoriaId)
            ->whereIn(
                'estado',
                [
                    'PUBLICADA',
                    'ACTIVA',
                ]
            )
            ->firstOrFail();

        $seguimiento = UsuarioConvocatoria::query()
            ->firstOrNew([
                'user_id' => $usuario->id,
                'convocatoria_id' => $convocatoria->id,
            ]);

        /*
        |--------------------------------------------------------------------------
        | No sobrescribir datos personales innecesariamente
        |--------------------------------------------------------------------------
        */

        if (! $seguimiento->exists) {
            $seguimiento->estado_seguimiento = 'GUARDADA';
            $seguimiento->prioridad = 'MEDIA';
        }

        $seguimiento->es_favorita = true;

        $seguimiento->ultima_accion =
            'Agregada desde Buscar Convocatorias';

        $seguimiento->fecha_ultima_accion = now();

        $seguimiento->save();

        Notification::make()
            ->title('Convocatoria guardada')
            ->body(
                'La convocatoria se agregó a Mis Convocatorias.'
            )
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $query = Convocatoria::query()
            ->with([
                'categoria',
                'organismo',
                'fuente',
            ])
            ->whereIn(
                'estado',
                [
                    'PUBLICADA',
                    'ACTIVA',
                ]
            );

        $this->aplicarBusqueda($query);

        $this->aplicarFiltros($query);

        $this->aplicarOrden($query);

        $totalResultados = (clone $query)->count();

        $usuarioId = auth()->id();

        $guardadasIds = $usuarioId
            ? UsuarioConvocatoria::query()
                ->where('user_id', $usuarioId)
                ->pluck('convocatoria_id')
                ->map(
                    fn ($id): int => (int) $id
                )
                ->all()
            : [];

        return [
            'convocatorias' => $query
                ->paginate(12),

            'totalResultados' => $totalResultados,

            'categorias' => Categoria::query()
                ->orderBy('nombre')
                ->get(),

            'organismos' => Organismo::query()
                ->orderBy('nombre')
                ->get(),

            'guardadasIds' => $guardadasIds,
        ];
    }

    private function aplicarBusqueda(
        Builder $query
    ): void {
        $buscar = trim($this->buscar);

        if ($buscar === '') {
            return;
        }

        $query->where(
            function (Builder $q) use ($buscar): void {
                $patron = '%' . $buscar . '%';

                $q->where(
                    'titulo',
                    'ilike',
                    $patron
                )
                    ->orWhere(
                        'descripcion',
                        'ilike',
                        $patron
                    )
                    ->orWhere(
                        'objetivo',
                        'ilike',
                        $patron
                    )
                    ->orWhereHas(
                        'organismo',
                        fn (Builder $organismo) =>
                            $organismo->where(
                                'nombre',
                                'ilike',
                                $patron
                            )
                    )
                    ->orWhereHas(
                        'categoria',
                        fn (Builder $categoria) =>
                            $categoria->where(
                                'nombre',
                                'ilike',
                                $patron
                            )
                    );
            }
        );
    }

    private function aplicarFiltros(
        Builder $query
    ): void {
        if ($this->categoria !== '') {
            $query->where(
                'categoria_id',
                (int) $this->categoria
            );
        }

        if ($this->organismo !== '') {
            $query->where(
                'organismo_id',
                (int) $this->organismo
            );
        }

        if (
            in_array(
                $this->estado,
                [
                    'PUBLICADA',
                    'ACTIVA',
                ],
                true
            )
        ) {
            $query->where(
                'estado',
                $this->estado
            );
        }

        match ($this->cierre) {
            '7' => $query
                ->whereNotNull('fecha_cierre')
                ->whereDate(
                    'fecha_cierre',
                    '>=',
                    today()
                )
                ->whereDate(
                    'fecha_cierre',
                    '<=',
                    today()->copy()->addDays(7)
                ),

            '30' => $query
                ->whereNotNull('fecha_cierre')
                ->whereDate(
                    'fecha_cierre',
                    '>=',
                    today()
                )
                ->whereDate(
                    'fecha_cierre',
                    '<=',
                    today()->copy()->addDays(30)
                ),

            '90' => $query
                ->whereNotNull('fecha_cierre')
                ->whereDate(
                    'fecha_cierre',
                    '>=',
                    today()
                )
                ->whereDate(
                    'fecha_cierre',
                    '<=',
                    today()->copy()->addDays(90)
                ),

            'sin_fecha' => $query
                ->whereNull('fecha_cierre'),

            default => null,
        };
    }

    private function aplicarOrden(
        Builder $query
    ): void {
        match ($this->orden) {
            'recientes' => $query
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('created_at'),

            'monto' => $query
                ->orderByDesc('monto_maximo')
                ->orderBy('titulo'),

            'titulo' => $query
                ->orderBy('titulo'),

            default => $query
                ->orderByRaw(
                    'fecha_cierre IS NULL, fecha_cierre ASC'
                )
                ->orderByDesc('created_at'),
        };
    }
}
