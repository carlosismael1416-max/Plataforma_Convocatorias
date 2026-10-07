<?php

namespace App\Filament\Admin\Pages;

use App\Models\ApiEvento;
use App\Models\EjecucionScraping;
use App\Models\ProcesamientoDocumento;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Panel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DetalleEjecucion extends Page
{
    protected string $view =
        'filament.admin.pages.detalle-ejecucion';

    protected static ?string $slug =
        'detalle-ejecucion/{record}';

    protected static bool $shouldRegisterNavigation =
        false;

    public string|int $record;

    public EjecucionScraping $ejecucion;

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

        $this->cargarEjecucion();
    }

    public static function getRelativeRouteName(
        Panel $panel
    ): string {
        return 'detalle-ejecucion';
    }

    public function getTitle(): string
    {
        return 'Detalle de Ejecución';
    }

    private function cargarEjecucion(): void
    {
        $this->ejecucion =
            EjecucionScraping::query()
                ->with([
                    'ejecucionesFuentes.fuente',
                    'errores.fuente',
                    'eventos',
                ])
                ->findOrFail(
                    $this->record
                );
    }

    protected function getViewData(): array
    {
        $this->cargarEjecucion();

        $ejecucion =
            $this->ejecucion;

        $detalleFuentes =
            $ejecucion
                ->ejecucionesFuentes
                ->sortBy(
                    'id'
                )
                ->values();

        $idsDetalles =
            $detalleFuentes
                ->pluck('id');

        $documentos =
            $idsDetalles->isEmpty()
                ? collect()
                : ProcesamientoDocumento::query()
                    ->with([
                        'ejecucionFuente.fuente',
                    ])
                    ->whereIn(
                        'ejecucion_fuente_id',
                        $idsDetalles
                    )
                    ->orderBy(
                        'fecha_inicio'
                    )
                    ->orderBy('id')
                    ->get();

        $eventosApi =
            ApiEvento::query()
                ->where(
                    'ejecucion_scraping_id',
                    $ejecucion->id
                )
                ->orderBy(
                    'fecha_envio'
                )
                ->orderBy('id')
                ->get();

        $errores =
            $ejecucion
                ->errores
                ->sortByDesc(
                    'created_at'
                )
                ->values();

        $porcentajeExito = 0;

        if (
            (int)
            $ejecucion->total_fuentes
            > 0
        ) {
            $porcentajeExito =
                round(
                    (
                        (int)
                        $ejecucion
                            ->fuentes_exitosas
                        /
                        (int)
                        $ejecucion
                            ->total_fuentes
                    )
                    * 100,
                    1
                );
        }

        $lineaTiempo = [];

        if ($ejecucion->fecha_inicio) {
            $lineaTiempo[] = [
                'fecha' =>
                    $ejecucion->fecha_inicio,

                'tipo' =>
                    'inicio',

                'titulo' =>
                    'Ciclo iniciado',

                'descripcion' =>
                    'Se registró el inicio del ciclo de extracción.',
            ];
        }

        foreach ($eventosApi as $evento) {
            $fecha =
                $evento->fecha_envio
                ?? $evento->created_at;

            if (! $fecha) {
                continue;
            }

            $lineaTiempo[] = [
                'fecha' =>
                    $fecha,

                'tipo' =>
                    'api',

                'titulo' =>
                    $evento->tipo_evento
                    ?: 'Evento API',

                'descripcion' =>
                    trim(
                        ($evento->metodo_http
                            ?: '')
                        .' '
                        .($evento->endpoint
                            ?: '')
                    ),
            ];
        }

        foreach ($errores as $error) {
            if (! $error->created_at) {
                continue;
            }

            $lineaTiempo[] = [
                'fecha' =>
                    $error->created_at,

                'tipo' =>
                    'error',

                'titulo' =>
                    $error->tipo_error
                    ?: 'Error registrado',

                'descripcion' =>
                    $error->mensaje,
            ];
        }

        if ($ejecucion->fecha_fin) {
            $lineaTiempo[] = [
                'fecha' =>
                    $ejecucion->fecha_fin,

                'tipo' =>
                    'fin',

                'titulo' =>
                    'Ciclo finalizado',

                'descripcion' =>
                    'Se registró la finalización del ciclo con estado '
                    .$ejecucion->estado
                    .'.',
            ];
        }

        usort(
            $lineaTiempo,
            fn (
                array $a,
                array $b
            ): int =>
                $a['fecha']->getTimestamp()
                <=>
                $b['fecha']->getTimestamp()
        );

        return [
            'ejecucion' =>
                $ejecucion,

            'detalleFuentes' =>
                $detalleFuentes,

            'documentos' =>
                $documentos,

            'errores' =>
                $errores,

            'eventosApi' =>
                $eventosApi,

            'porcentajeExito' =>
                $porcentajeExito,

            'lineaTiempo' =>
                $lineaTiempo,
        ];
    }

    public function descargarResumen(): StreamedResponse
    {
        $this->cargarEjecucion();

        $ejecucion =
            $this->ejecucion;

        $ejecucion->load([
            'ejecucionesFuentes.fuente',
            'errores.fuente',
            'eventos',
        ]);

        $idsDetalles =
            $ejecucion
                ->ejecucionesFuentes
                ->pluck('id');

        $documentos =
            $idsDetalles->isEmpty()
                ? collect()
                : ProcesamientoDocumento::query()
                    ->whereIn(
                        'ejecucion_fuente_id',
                        $idsDetalles
                    )
                    ->get();

        $resumen = [
            'ejecucion' => [
                'id' =>
                    $ejecucion->id,

                'estado' =>
                    $ejecucion->estado,

                'fecha_inicio' =>
                    $ejecucion
                        ->fecha_inicio
                        ?->toIso8601String(),

                'fecha_fin' =>
                    $ejecucion
                        ->fecha_fin
                        ?->toIso8601String(),

                'duracion_segundos' =>
                    $ejecucion
                        ->duracion_segundos,

                'total_fuentes' =>
                    $ejecucion
                        ->total_fuentes,

                'fuentes_exitosas' =>
                    $ejecucion
                        ->fuentes_exitosas,

                'fuentes_fallidas' =>
                    $ejecucion
                        ->fuentes_fallidas,

                'total_encontradas' =>
                    $ejecucion
                        ->total_encontradas,

                'nuevas_convocatorias' =>
                    $ejecucion
                        ->nuevas_convocatorias,

                'convocatorias_actualizadas' =>
                    $ejecucion
                        ->convocatorias_actualizadas,

                'duplicados_detectados' =>
                    $ejecucion
                        ->duplicados_detectados,

                'total_errores' =>
                    $ejecucion
                        ->total_errores,
            ],

            'fuentes' =>
                $ejecucion
                    ->ejecucionesFuentes
                    ->map(
                        fn ($detalle) => [
                            'id' =>
                                $detalle->id,

                            'fuente_id' =>
                                $detalle->fuente_id,

                            'fuente' =>
                                $detalle
                                    ->fuente
                                    ?->nombre,

                            'estado' =>
                                $detalle->estado,

                            'encontrados' =>
                                $detalle
                                    ->registros_encontrados,

                            'nuevos' =>
                                $detalle
                                    ->registros_nuevos,

                            'actualizados' =>
                                $detalle
                                    ->registros_actualizados,

                            'duplicados' =>
                                $detalle->duplicados,

                            'errores' =>
                                $detalle->errores,

                            'http_status' =>
                                $detalle
                                    ->http_status,

                            'duracion_segundos' =>
                                $detalle
                                    ->duracion_segundos,
                        ]
                    )
                    ->values()
                    ->all(),

            'documentos_procesados' =>
                $documentos
                    ->map(
                        fn ($documento) => [
                            'id' =>
                                $documento->id,

                            'convocatoria_archivo_id' =>
                                $documento
                                    ->convocatoria_archivo_id,

                            'motor_extraccion' =>
                                $documento
                                    ->motor_extraccion,

                            'estado' =>
                                $documento->estado,

                            'paginas' =>
                                $documento->paginas,

                            'requiere_ocr' =>
                                $documento
                                    ->requiere_ocr,

                            'mensaje_error' =>
                                $documento
                                    ->mensaje_error,
                        ]
                    )
                    ->values()
                    ->all(),

            'errores' =>
                $ejecucion
                    ->errores
                    ->map(
                        fn ($error) => [
                            'id' =>
                                $error->id,

                            'tipo' =>
                                $error
                                    ->tipo_error,

                            'codigo' =>
                                $error
                                    ->codigo_error,

                            'mensaje' =>
                                $error->mensaje,

                            'fuente' =>
                                $error
                                    ->fuente
                                    ?->nombre,

                            'resuelto' =>
                                $error->resuelto,
                        ]
                    )
                    ->values()
                    ->all(),

            'eventos_api' =>
                $ejecucion
                    ->eventos
                    ->map(
                        fn ($evento) => [
                            'id' =>
                                $evento->id,

                            'tipo' =>
                                $evento
                                    ->tipo_evento,

                            'endpoint' =>
                                $evento->endpoint,

                            'metodo' =>
                                $evento
                                    ->metodo_http,

                            'codigo_respuesta' =>
                                $evento
                                    ->codigo_respuesta,

                            'estado' =>
                                $evento->estado,

                            'intentos' =>
                                $evento->intentos,
                        ]
                    )
                    ->values()
                    ->all(),
        ];

        $json =
            json_encode(
                $resumen,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

        return response()->streamDownload(
            function () use ($json): void {
                echo $json;
            },
            'ejecucion-'
            .$ejecucion->id
            .'-resumen.json',
            [
                'Content-Type' =>
                    'application/json; charset=UTF-8',
            ]
        );
    }
}
