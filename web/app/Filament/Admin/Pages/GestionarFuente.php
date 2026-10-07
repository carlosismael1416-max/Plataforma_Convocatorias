<?php

namespace App\Filament\Admin\Pages;

use App\Models\BitacoraError;
use App\Models\EjecucionFuente;
use App\Models\Fuente;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;

class GestionarFuente extends Page
{
    protected string $view =
        'filament.admin.pages.gestionar-fuente';

    protected static ?string $slug =
        'gestionar-fuente/{record}';

    protected static bool $shouldRegisterNavigation =
        false;

    public string|int $record;

    public Fuente $fuente;

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

        $this->fuente =
            Fuente::query()
                ->findOrFail(
                    $record
                );
    }

    public static function getRelativeRouteName(
        Panel $panel
    ): string {
        return 'gestionar-fuente';
    }

    public function getTitle(): string
    {
        return 'Gestionar Fuente';
    }

    public function alternarEstado(): void
    {
        $fuente =
            Fuente::query()
                ->findOrFail(
                    $this->fuente->id
                );

        $nuevoEstado =
            ! (bool) $fuente->activa;

        $fuente->update([
            'activa' =>
                $nuevoEstado,
        ]);

        $this->fuente =
            $fuente->fresh();

        Notification::make()
            ->title(
                $nuevoEstado
                    ? 'Fuente activada'
                    : 'Fuente desactivada'
            )
            ->body(
                $nuevoEstado
                    ? 'La fuente podrá participar en los ciclos de extracción.'
                    : 'La fuente quedó fuera de los ciclos automáticos.'
            )
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $fuente =
            Fuente::query()
                ->findOrFail(
                    $this->fuente->id
                );

        $this->fuente =
            $fuente;

        $ejecuciones =
            EjecucionFuente::query()
                ->with([
                    'ejecucionScraping',
                ])
                ->where(
                    'fuente_id',
                    $fuente->id
                )
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(5)
                ->get();

        $errores =
            BitacoraError::query()
                ->where(
                    'fuente_id',
                    $fuente->id
                )
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(5)
                ->get();

        $totalEjecuciones =
            EjecucionFuente::query()
                ->where(
                    'fuente_id',
                    $fuente->id
                )
                ->count();

        $totalErrores =
            BitacoraError::query()
                ->where(
                    'fuente_id',
                    $fuente->id
                )
                ->count();

        $erroresPendientes =
            BitacoraError::query()
                ->where(
                    'fuente_id',
                    $fuente->id
                )
                ->where(
                    function ($query) {
                        $query
                            ->where(
                                'resuelto',
                                false
                            )
                            ->orWhereNull(
                                'resuelto'
                            );
                    }
                )
                ->count();

        $ultimaEjecucion =
            $fuente->ultima_ejecucion;

        if (
            $ultimaEjecucion === null
            && $ejecuciones->isNotEmpty()
        ) {
            $ultimaEjecucion =
                $ejecuciones
                    ->first()
                    ->fecha_inicio;
        }

        $selectorConfig =
            $fuente->selector_config;

        $selectorJson =
            ! empty($selectorConfig)
                ? json_encode(
                    $selectorConfig,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                )
                : null;

        $urlSegura =
            $this->urlSegura(
                $fuente->url_base
            );

        return [
            'fuente' =>
                $fuente,

            'ejecuciones' =>
                $ejecuciones,

            'errores' =>
                $errores,

            'totalEjecuciones' =>
                $totalEjecuciones,

            'totalErrores' =>
                $totalErrores,

            'erroresPendientes' =>
                $erroresPendientes,

            'ultimaEjecucion' =>
                $ultimaEjecucion,

            'selectorJson' =>
                $selectorJson,

            'urlSegura' =>
                $urlSegura,
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
