<?php

namespace App\Filament\Docente\Pages;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;

class DetalleConvocatoria extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug =
        'detalle-convocatoria/{record}';

    protected static ?string $title =
        'Detalle de convocatoria';

    protected string $view =
        'filament.docente.pages.detalle-convocatoria';

    public Convocatoria $convocatoria;

    public string $tab = 'informacion';

    public bool $guardada = false;

    public bool $tienePropuesta = false;

    public static function getRelativeRouteName(
        Panel $panel
    ): string {
        return 'detalle-convocatoria';
    }

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function mount(int|string $record): void
    {
        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | El Docente únicamente puede consultar convocatorias que ya hayan
        | sido publicadas o activadas.
        |
        */

        $this->convocatoria = Convocatoria::query()
            ->whereKey($record)
            ->whereIn(
                'estado',
                [
                    'PUBLICADA',
                    'ACTIVA',
                ]
            )
            ->with([
                'categoria',
                'organismo',
                'fuente',
                'requisitos',
                'archivos',
                'financiamientos',
                'modalidadesFinanciamiento.etapas',
                'apoyosFinancieros',
            ])
            ->firstOrFail();

        $usuarioId = auth()->id();

        $this->guardada = UsuarioConvocatoria::query()
            ->where('user_id', $usuarioId)
            ->where(
                'convocatoria_id',
                $this->convocatoria->id
            )
            ->exists();

        $this->tienePropuesta = Propuesta::query()
            ->where('user_id', $usuarioId)
            ->where(
                'convocatoria_id',
                $this->convocatoria->id
            )
            ->exists();
    }

    public function guardarConvocatoria(): void
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario instanceof User
                && (bool) $usuario->estado
                && $usuario->tieneRol('DOCENTE'),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Comprobar de nuevo el estado
        |--------------------------------------------------------------------------
        |
        | No confiamos únicamente en el registro cargado durante mount().
        | Si el estado cambia mientras el usuario está en la página, la acción
        | tampoco debe permitir guardar una convocatoria no disponible.
        |
        */

        $convocatoria = Convocatoria::query()
            ->whereKey($this->convocatoria->id)
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

        if (! $seguimiento->exists) {
            $seguimiento->estado_seguimiento = 'GUARDADA';
            $seguimiento->prioridad = 'MEDIA';
        }

        $seguimiento->es_favorita = true;

        $seguimiento->ultima_accion =
            'Agregada desde Detalle de Convocatoria';

        $seguimiento->fecha_ultima_accion = now();

        $seguimiento->save();

        $this->guardada = true;

        Notification::make()
            ->title('Convocatoria guardada')
            ->body(
                'Se agregó correctamente a Mis Convocatorias.'
            )
            ->success()
            ->send();
    }
}
