<?php

namespace App\Filament\Docente\Pages;

use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class MisConvocatorias extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBookmark;

    protected static ?string $navigationLabel =
        'Mis convocatorias';

    protected static ?string $title =
        'Mis convocatorias';

    protected static ?int $navigationSort = 2;

    protected string $view =
        'filament.docente.pages.mis-convocatorias';

    public string $buscar = '';

    public string $estado = '';

    public string $prioridad = '';

    public string $disponibilidad = '';

    public array $notas = [];

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function mount(): void
    {
        $usuarioId = auth()->id();

        if (! $usuarioId) {
            return;
        }

        $this->notas = UsuarioConvocatoria::query()
            ->where('user_id', $usuarioId)
            ->pluck('notas', 'id')
            ->mapWithKeys(
                fn ($nota, $id): array => [
                    (int) $id => (string) ($nota ?? ''),
                ]
            )
            ->all();
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedPrioridad(): void
    {
        $this->resetPage();
    }

    public function updatedDisponibilidad(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->estado = '';
        $this->prioridad = '';
        $this->disponibilidad = '';

        $this->resetPage();
    }

    public function quitar(int $seguimientoId): void
    {
        $seguimiento = $this->seguimientoDelUsuario(
            $seguimientoId
        );

        $seguimiento->delete();

        unset($this->notas[$seguimientoId]);

        Notification::make()
            ->title('Convocatoria eliminada')
            ->body(
                'Se eliminó de Mis Convocatorias.'
            )
            ->success()
            ->send();

        $this->resetPage();
    }

    public function cambiarFavorita(
        int $seguimientoId
    ): void {
        $seguimiento = $this->seguimientoDelUsuario(
            $seguimientoId
        );

        $seguimiento->es_favorita =
            ! $seguimiento->es_favorita;

        $seguimiento->fecha_ultima_accion = now();

        $seguimiento->ultima_accion =
            $seguimiento->es_favorita
                ? 'Marcada como favorita'
                : 'Quitada de favoritas';

        $seguimiento->save();

        Notification::make()
            ->title(
                $seguimiento->es_favorita
                    ? 'Marcada como favorita'
                    : 'Quitada de favoritas'
            )
            ->success()
            ->send();
    }

    public function actualizarPrioridad(
        int $seguimientoId,
        string $prioridad
    ): void {
        abort_unless(
            in_array(
                $prioridad,
                [
                    'BAJA',
                    'MEDIA',
                    'ALTA',
                ],
                true
            ),
            422
        );

        $seguimiento = $this->seguimientoDelUsuario(
            $seguimientoId
        );

        $seguimiento->prioridad = $prioridad;

        $seguimiento->ultima_accion =
            'Prioridad actualizada a ' . $prioridad;

        $seguimiento->fecha_ultima_accion = now();

        $seguimiento->save();

        Notification::make()
            ->title('Prioridad actualizada')
            ->success()
            ->send();
    }

    public function guardarNotas(
        int $seguimientoId
    ): void {
        $seguimiento = $this->seguimientoDelUsuario(
            $seguimientoId
        );

        $nota = trim(
            (string) (
                $this->notas[$seguimientoId]
                ?? ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Límite defensivo
        |--------------------------------------------------------------------------
        */

        $nota = mb_substr(
            $nota,
            0,
            5000
        );

        $seguimiento->notas =
            $nota !== ''
                ? $nota
                : null;

        $seguimiento->ultima_accion =
            'Notas actualizadas';

        $seguimiento->fecha_ultima_accion = now();

        $seguimiento->save();

        Notification::make()
            ->title('Notas guardadas')
            ->success()
            ->send();
    }

    public function verDetalle(
        int $seguimientoId
    ): mixed {
        $seguimiento = $this->seguimientoDelUsuario(
            $seguimientoId
        );

        $convocatoria = $seguimiento->convocatoria;

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
                    'Convocatoria todavía no disponible'
                )
                ->body(
                    'La convocatoria debe estar publicada o activa para consultar su detalle.'
                )
                ->warning()
                ->send();

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Seguimiento automático
        |--------------------------------------------------------------------------
        */

        if (
            $seguimiento->estado_seguimiento
            === 'GUARDADA'
        ) {
            $seguimiento->estado_seguimiento =
                'REVISANDO';

            $seguimiento->ultima_accion =
                'Consultó el detalle de la convocatoria';

            $seguimiento->fecha_ultima_accion =
                now();

            $seguimiento->save();
        }

        return redirect()->route(
            'filament.docente.pages.detalle-convocatoria',
            [
                'record' => $convocatoria->id,
            ]
        );
    }

    public function irAPropuesta(
        int $seguimientoId
    ): mixed {
        $seguimiento = $this->seguimientoDelUsuario(
            $seguimientoId
        );

        $convocatoria = $seguimiento->convocatoria;

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
                    'No se puede generar la propuesta'
                )
                ->body(
                    'La convocatoria todavía no ha sido publicada o activada.'
                )
                ->warning()
                ->send();

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | No sobrescribir estados posteriores del flujo
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $seguimiento->estado_seguimiento,
                [
                    'GUARDADA',
                    'REVISANDO',
                ],
                true
            )
        ) {
            $seguimiento->estado_seguimiento =
                'PREPARANDO_PROPUESTA';
        }

        $seguimiento->ultima_accion =
            'Accedió a la preparación de propuesta';

        $seguimiento->fecha_ultima_accion =
            now();

        $seguimiento->save();

        return redirect()->route(
            'filament.docente.pages.generar-propuesta',
            [
                'convocatoria' => $convocatoria->id,
            ]
        );
    }

    protected function getViewData(): array
    {
        $usuarioId = auth()->id();

        $query = UsuarioConvocatoria::query()
            ->with([
                'convocatoria.categoria',
                'convocatoria.organismo',
            ])
            ->where('user_id', $usuarioId)
            ->whereHas('convocatoria');

        /*
        |--------------------------------------------------------------------------
        | Búsqueda
        |--------------------------------------------------------------------------
        */

        $buscar = trim($this->buscar);

        if ($buscar !== '') {
            $query->whereHas(
                'convocatoria',
                function (
                    Builder $convocatoria
                ) use ($buscar): void {
                    $patron =
                        '%' . $buscar . '%';

                    $convocatoria
                        ->where(
                            'titulo',
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

        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $this->estado,
                [
                    'GUARDADA',
                    'REVISANDO',
                    'PREPARANDO_PROPUESTA',
                    'ENVIADA',
                    'ACEPTADA',
                    'RECHAZADA',
                    'FINALIZADA',
                ],
                true
            )
        ) {
            $query->where(
                'estado_seguimiento',
                $this->estado
            );
        }

        if (
            in_array(
                $this->prioridad,
                [
                    'BAJA',
                    'MEDIA',
                    'ALTA',
                ],
                true
            )
        ) {
            $query->where(
                'prioridad',
                $this->prioridad
            );
        }

        if (
            $this->disponibilidad
            === 'disponible'
        ) {
            $query->whereHas(
                'convocatoria',
                fn (Builder $convocatoria) =>
                    $convocatoria->whereIn(
                        'estado',
                        [
                            'PUBLICADA',
                            'ACTIVA',
                        ]
                    )
            );
        }

        if (
            $this->disponibilidad
            === 'no_disponible'
        ) {
            $query->whereHas(
                'convocatoria',
                fn (Builder $convocatoria) =>
                    $convocatoria->whereNotIn(
                        'estado',
                        [
                            'PUBLICADA',
                            'ACTIVA',
                        ]
                    )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Propuestas existentes del usuario
        |--------------------------------------------------------------------------
        */

        $convocatoriasConPropuesta =
            Propuesta::query()
                ->where(
                    'user_id',
                    $usuarioId
                )
                ->pluck('convocatoria_id')
                ->map(
                    fn ($id): int => (int) $id
                )
                ->all();

        /*
        |--------------------------------------------------------------------------
        | Estadísticas personales
        |--------------------------------------------------------------------------
        */

        $base = UsuarioConvocatoria::query()
            ->where('user_id', $usuarioId)
            ->whereHas('convocatoria');

        $total = (clone $base)->count();

        $favoritas = (clone $base)
            ->where('es_favorita', true)
            ->count();

        $preparando = (clone $base)
            ->where(
                'estado_seguimiento',
                'PREPARANDO_PROPUESTA'
            )
            ->count();

        $noDisponibles = (clone $base)
            ->whereHas(
                'convocatoria',
                fn (Builder $convocatoria) =>
                    $convocatoria->whereNotIn(
                        'estado',
                        [
                            'PUBLICADA',
                            'ACTIVA',
                        ]
                    )
            )
            ->count();

        return [
            'seguimientos' => $query
                ->orderByDesc(
                    'fecha_actualizacion'
                )
                ->paginate(10),

            'convocatoriasConPropuesta' =>
                $convocatoriasConPropuesta,

            'total' => $total,

            'favoritas' => $favoritas,

            'preparando' => $preparando,

            'noDisponibles' => $noDisponibles,
        ];
    }

    private function seguimientoDelUsuario(
        int $seguimientoId
    ): UsuarioConvocatoria {
        return UsuarioConvocatoria::query()
            ->with('convocatoria')
            ->whereKey($seguimientoId)
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();
    }
}
