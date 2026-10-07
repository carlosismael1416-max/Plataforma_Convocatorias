<?php

namespace App\Filament\Docente\Pages;

use App\Models\Propuesta;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class Propuestas extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel =
        'Mis propuestas';

    protected static ?string $title =
        'Mis propuestas';

    protected static ?int $navigationSort = 4;

    protected string $view =
        'filament.docente.pages.propuestas';

    public string $buscar = '';

    public string $estado = '';

    public string $orden = 'actualizacion';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
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
        $this->estado = '';
        $this->orden = 'actualizacion';

        $this->resetPage();
    }

    public function continuar(
        int $propuestaId
    ): mixed {
        $propuesta = $this->propuestaDelUsuario(
            $propuestaId
        );

        return redirect()->route(
            'filament.docente.pages.editor-propuesta',
            [
                'propuesta' => $propuesta->id,
            ]
        );
    }

    public function vistaPrevia(
        int $propuestaId
    ): mixed {
        $propuesta = $this->propuestaDelUsuario(
            $propuestaId
        );

        return redirect()->route(
            'filament.docente.pages.vista-previa-propuesta',
            [
                'propuesta' => $propuesta->id,
            ]
        );
    }

    public function verConvocatoria(
        int $propuestaId
    ): mixed {
        $propuesta = $this->propuestaDelUsuario(
            $propuestaId
        );

        $convocatoria = $propuesta->convocatoria;

        if (
            ! $convocatoria
            || ! in_array(
                $convocatoria->estado,
                [
                    'PUBLICADA',
                    'ACTIVA',
                ],
                true
            )
        ) {
            Notification::make()
                ->title(
                    'Convocatoria no disponible'
                )
                ->body(
                    'La convocatoria asociada no está publicada o activa.'
                )
                ->warning()
                ->send();

            return null;
        }

        return redirect()->route(
            'filament.docente.pages.detalle-convocatoria',
            [
                'record' => $convocatoria->id,
            ]
        );
    }

    protected function getViewData(): array
    {
        $usuarioId = auth()->id();

        $query = Propuesta::query()
            ->with([
                'convocatoria.organismo',
                'convocatoria.categoria',
            ])
            ->where(
                'user_id',
                $usuarioId
            );

        /*
        |--------------------------------------------------------------------------
        | Búsqueda
        |--------------------------------------------------------------------------
        */

        $buscar = trim($this->buscar);

        if ($buscar !== '') {
            $query->where(
                function (
                    Builder $q
                ) use ($buscar): void {
                    $patron =
                        '%' . $buscar . '%';

                    $q->where(
                        'titulo',
                        'ilike',
                        $patron
                    )
                        ->orWhereHas(
                            'convocatoria',
                            function (
                                Builder $convocatoria
                            ) use ($patron): void {
                                $convocatoria
                                    ->where(
                                        'titulo',
                                        'ilike',
                                        $patron
                                    )
                                    ->orWhereHas(
                                        'organismo',
                                        fn (
                                            Builder $organismo
                                        ) =>
                                            $organismo->where(
                                                'nombre',
                                                'ilike',
                                                $patron
                                            )
                                    );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro por estado
        |--------------------------------------------------------------------------
        */

        $estadosPermitidos = [
            'BORRADOR',
            'GENERADA',
            'EDITANDO',
            'LISTA',
            'ENVIADA',
            'APROBADA',
            'RECHAZADA',
        ];

        if (
            in_array(
                $this->estado,
                $estadosPermitidos,
                true
            )
        ) {
            $query->where(
                'estado',
                $this->estado
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Orden
        |--------------------------------------------------------------------------
        */

        match ($this->orden) {
            'creacion' =>
                $query->orderByDesc(
                    'created_at'
                ),

            'titulo' =>
                $query->orderBy(
                    'titulo'
                ),

            'estado' =>
                $query
                    ->orderBy('estado')
                    ->orderByDesc(
                        'updated_at'
                    ),

            default =>
                $query->orderByDesc(
                    'updated_at'
                ),
        };

        /*
        |--------------------------------------------------------------------------
        | Estadísticas del usuario
        |--------------------------------------------------------------------------
        */

        $base = Propuesta::query()
            ->where(
                'user_id',
                $usuarioId
            );

        $total = (clone $base)->count();

        $enProceso = (clone $base)
            ->whereIn(
                'estado',
                [
                    'BORRADOR',
                    'GENERADA',
                    'EDITANDO',
                ]
            )
            ->count();

        $listas = (clone $base)
            ->where(
                'estado',
                'LISTA'
            )
            ->count();

        $enviadas = (clone $base)
            ->whereIn(
                'estado',
                [
                    'ENVIADA',
                    'APROBADA',
                ]
            )
            ->count();

        return [
            'propuestas' =>
                $query->paginate(10),

            'total' => $total,

            'enProceso' =>
                $enProceso,

            'listas' => $listas,

            'enviadas' =>
                $enviadas,
        ];
    }

    private function propuestaDelUsuario(
        int $propuestaId
    ): Propuesta {
        return Propuesta::query()
            ->with('convocatoria')
            ->whereKey(
                $propuestaId
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();
    }
}
