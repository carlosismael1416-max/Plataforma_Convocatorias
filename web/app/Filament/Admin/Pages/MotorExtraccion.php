<?php

namespace App\Filament\Admin\Pages;

use App\Models\BitacoraError;
use App\Models\EjecucionScraping;
use App\Models\Fuente;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class MotorExtraccion extends Page
{
    protected string $view =
        'filament.admin.pages.motor-extraccion';

    protected static ?string $slug =
        'motor-extraccion';

    protected static ?string $navigationLabel =
        'Motor de Extracción';

    protected static ?int $navigationSort =
        5;

    public ?int $fuente = null;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    public function mount(): void
    {
        $fuente =
            request()->query(
                'fuente'
            );

        if (
            is_numeric($fuente)
            && Fuente::query()
                ->whereKey(
                    (int) $fuente
                )
                ->exists()
        ) {
            $this->fuente =
                (int) $fuente;
        }
    }

    public function ejecutarCiclo(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EVITAR EJECUCIONES SIMULTÁNEAS
        |--------------------------------------------------------------------------
        */

        $hayEjecucionActiva =
            EjecucionScraping::query()
                ->whereIn(
                    'estado',
                    [
                        'INICIADO',
                        'EJECUTANDO',
                    ]
                )
                ->exists();

        if ($hayEjecucionActiva) {
            Notification::make()
                ->title(
                    'El motor ya está trabajando'
                )
                ->body(
                    'Espera a que termine la ejecución actual antes de iniciar otra.'
                )
                ->warning()
                ->send();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FUENTE A EJECUTAR
        |--------------------------------------------------------------------------
        */

        if ($this->fuente) {
            $fuente =
                Fuente::query()
                    ->find(
                        $this->fuente
                    );
        } else {
            /*
             * Mientras el motor JavaScript no esté
             * implementado, seleccionamos únicamente
             * una fuente activa compatible con Scrapy.
             */
            $fuente =
                Fuente::query()
                    ->where(
                        'activa',
                        true
                    )
                    ->where(
                        'requiere_javascript',
                        false
                    )
                    ->orderBy('id')
                    ->first();
        }


        if (! $fuente) {
            Notification::make()
                ->title(
                    'No hay una fuente disponible'
                )
                ->body(
                    'No existe una fuente activa compatible con el extractor actual.'
                )
                ->warning()
                ->send();

            return;
        }


        if (! $fuente->activa) {
            Notification::make()
                ->title(
                    'Fuente inactiva'
                )
                ->body(
                    'La fuente seleccionada está desactivada.'
                )
                ->warning()
                ->send();

            return;
        }


        if ($fuente->requiere_javascript) {
            Notification::make()
                ->title(
                    'Fuente todavía no compatible'
                )
                ->body(
                    'Esta fuente requiere JavaScript. Su motor de extracción se integrará posteriormente.'
                )
                ->warning()
                ->send();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | FASTAPI
        |--------------------------------------------------------------------------
        */

        $apiUrl =
            rtrim(
                (string) config(
                    'services.extractor.url',
                    'http://127.0.0.1:8001'
                ),
                '/'
            );

        try {
            $respuesta =
                Http::acceptJson()
                    ->timeout(5)
                    ->post(
                        $apiUrl
                        . '/scraping/ejecutar',
                        [
                            'fuente_id' =>
                                $fuente->id,

                            'limite' =>
                                5,

                            'guardar' =>
                                true,
                        ]
                    );

        } catch (\Throwable $error) {
            report($error);

            Notification::make()
                ->title(
                    'Motor de extracción no disponible'
                )
                ->body(
                    'Laravel no pudo comunicarse con FastAPI. Verifica que el servicio esté ejecutándose.'
                )
                ->danger()
                ->send();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPUESTA DEL EXTRACTOR
        |--------------------------------------------------------------------------
        */

        if ($respuesta->status() !== 202) {
            $detalle =
                $respuesta->json(
                    'detail'
                );

            if (
                ! is_string($detalle)
                || trim($detalle) === ''
            ) {
                $detalle =
                    'FastAPI respondió con HTTP '
                    . $respuesta->status()
                    . '.';
            }

            Notification::make()
                ->title(
                    'No se pudo iniciar el ciclo'
                )
                ->body($detalle)
                ->danger()
                ->send();

            return;
        }


        Notification::make()
            ->title(
                'Ciclo de extracción iniciado'
            )
            ->body(
                'Se inició la extracción de '
                . $fuente->nombre
                . '. El proceso continuará en segundo plano.'
            )
            ->success()
            ->send();
    }


    public function getTitle(): string
    {
        return 'Motor de Extracción';
    }

    protected function getViewData(): array
    {
        $fuenteContexto =
            $this->fuente
                ? Fuente::query()
                    ->find(
                        $this->fuente
                    )
                : null;

        /*
        |--------------------------------------------------------------------------
        | ESTADÍSTICAS GENERALES
        |--------------------------------------------------------------------------
        */

        $totalEjecuciones =
            EjecucionScraping::query()
                ->count();

        $totalFuentes =
            Fuente::query()
                ->count();

        $fuentesActivas =
            Fuente::query()
                ->where(
                    'activa',
                    true
                )
                ->count();

        $totalDetectadas =
            (int)
            EjecucionScraping::query()
                ->sum(
                    'total_encontradas'
                );

        $erroresPendientes =
            BitacoraError::query()
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

        /*
        |--------------------------------------------------------------------------
        | EJECUCIONES
        |--------------------------------------------------------------------------
        */

        $baseEjecuciones =
            EjecucionScraping::query();

        if ($fuenteContexto) {
            $baseEjecuciones
                ->whereHas(
                    'ejecucionesFuentes',
                    fn ($query) =>
                        $query->where(
                            'fuente_id',
                            $fuenteContexto->id
                        )
                );
        }

        $ejecuciones =
            (clone $baseEjecuciones)
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(6)
                ->get();

        $ultimaEjecucion =
            (clone $baseEjecuciones)
                ->with([
                    'ejecucionesFuentes.fuente',
                ])
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc(
                    'id'
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | EJECUCIÓN ACTIVA
        |--------------------------------------------------------------------------
        */

        $ejecucionActiva =
            EjecucionScraping::query()
                ->whereIn(
                    'estado',
                    [
                        'INICIADO',
                        'EJECUTANDO',
                    ]
                )
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | ALERTAS
        |--------------------------------------------------------------------------
        */

        $alertasQuery =
            BitacoraError::query()
                ->with([
                    'fuente',
                ])
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
                );

        if ($fuenteContexto) {
            $alertasQuery
                ->where(
                    'fuente_id',
                    $fuenteContexto->id
                );
        }

        $alertas =
            $alertasQuery
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(5)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | PROGRESO DE ÚLTIMA EJECUCIÓN
        |--------------------------------------------------------------------------
        */

        $porcentajeExito = 0;

        if (
            $ultimaEjecucion
            && (int)
                $ultimaEjecucion
                    ->total_fuentes
                > 0
        ) {
            $porcentajeExito =
                round(
                    (
                        (int)
                        $ultimaEjecucion
                            ->fuentes_exitosas

                        /

                        (int)
                        $ultimaEjecucion
                            ->total_fuentes
                    )
                    * 100,
                    1
                );
        }

        return [
            'fuenteContexto' =>
                $fuenteContexto,

            'totalEjecuciones' =>
                $totalEjecuciones,

            'totalFuentes' =>
                $totalFuentes,

            'fuentesActivas' =>
                $fuentesActivas,

            'totalDetectadas' =>
                $totalDetectadas,

            'erroresPendientes' =>
                $erroresPendientes,

            'ejecuciones' =>
                $ejecuciones,

            'ultimaEjecucion' =>
                $ultimaEjecucion,

            'ejecucionActiva' =>
                $ejecucionActiva,

            'alertas' =>
                $alertas,

            'porcentajeExito' =>
                $porcentajeExito,
        ];
    }
}
