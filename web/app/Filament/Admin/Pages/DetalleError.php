<?php

namespace App\Filament\Admin\Pages;

use App\Models\BitacoraError;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;

class DetalleError extends Page
{
    protected string $view =
        'filament.admin.pages.detalle-error';

    protected static ?string $slug =
        'detalle-error/{record}';

    protected static bool $shouldRegisterNavigation =
        false;

    public string|int $record;

    public BitacoraError $error;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    public function mount(
        string|int $record
    ): void {
        $this->record = $record;

        $this->cargarError();
    }

    public static function getRelativeRouteName(
        Panel $panel
    ): string {
        return 'detalle-error';
    }

    public function getTitle(): string
    {
        return 'Detalle del Error';
    }

    private function cargarError(): void
    {
        $this->error =
            BitacoraError::query()
                ->with([
                    'fuente',
                    'ejecucionScraping',
                    'ejecucionFuente',
                    'usuarioResolucion',
                ])
                ->findOrFail(
                    $this->record
                );
    }

    public function marcarComoResuelto(): void
    {
        $this->cargarError();

        if ($this->error->resuelto) {
            return;
        }

        $this->error->update([
            'resuelto' =>
                true,

            'fecha_resolucion' =>
                now(),

            'user_id_resolucion' =>
                auth()->id(),
        ]);

        $this->cargarError();

        Notification::make()
            ->title(
                'Incidencia resuelta'
            )
            ->body(
                'El error fue marcado como resuelto correctamente.'
            )
            ->success()
            ->send();
    }

    public function reabrirIncidencia(): void
    {
        $this->cargarError();

        if (! $this->error->resuelto) {
            return;
        }

        $this->error->update([
            'resuelto' =>
                false,

            'fecha_resolucion' =>
                null,

            'user_id_resolucion' =>
                null,
        ]);

        $this->cargarError();

        Notification::make()
            ->title(
                'Incidencia reabierta'
            )
            ->body(
                'El error volvió al estado pendiente.'
            )
            ->warning()
            ->send();
    }

    protected function getViewData(): array
    {
        $this->cargarError();

        $error =
            $this->error;

        $urlSegura =
            $this->urlSegura(
                $error->url
            );

        $eventos = [];

        if (
            $error->ejecucionFuente
            ?->fecha_inicio
        ) {
            $eventos[] = [
                'tipo' =>
                    'inicio',

                'titulo' =>
                    'Inicio de procesamiento de la fuente',

                'texto' =>
                    'La fuente comenzó a procesarse.',

                'fecha' =>
                    $error
                        ->ejecucionFuente
                        ->fecha_inicio,
            ];
        }

        if ($error->created_at) {
            $eventos[] = [
                'tipo' =>
                    'error',

                'titulo' =>
                    $error->tipo_error
                    ?: 'Error registrado',

                'texto' =>
                    $error->mensaje,

                'fecha' =>
                    $error->created_at,
            ];
        }

        if (
            $error->resuelto
            && $error->fecha_resolucion
        ) {
            $nombreUsuario =
                $error
                    ->usuarioResolucion
                    ?->name;

            $eventos[] = [
                'tipo' =>
                    'resuelto',

                'titulo' =>
                    'Incidencia resuelta',

                'texto' =>
                    $nombreUsuario
                        ? 'Marcada como resuelta por '
                            .$nombreUsuario
                            .'.'
                        : 'La incidencia fue marcada como resuelta.',

                'fecha' =>
                    $error
                        ->fecha_resolucion,
            ];
        }

        usort(
            $eventos,
            fn (
                array $a,
                array $b
            ): int =>
                $a['fecha']->getTimestamp()
                <=>
                $b['fecha']->getTimestamp()
        );

        return [
            'error' =>
                $error,

            'urlSegura' =>
                $urlSegura,

            'eventos' =>
                $eventos,
        ];
    }

    private function urlSegura(
        ?string $url
    ): ?string {
        $url =
            trim(
                (string) $url
            );

        if ($url === '') {
            return null;
        }

        if (
            ! filter_var(
                $url,
                FILTER_VALIDATE_URL
            )
        ) {
            return null;
        }

        $scheme =
            strtolower(
                (string)
                parse_url(
                    $url,
                    PHP_URL_SCHEME
                )
            );

        if (
            ! in_array(
                $scheme,
                [
                    'http',
                    'https',
                ],
                true
            )
        ) {
            return null;
        }

        return $url;
    }
}
