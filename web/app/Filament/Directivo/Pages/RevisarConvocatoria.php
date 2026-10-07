<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Str;

class RevisarConvocatoria extends Page
{
    protected string $view =
        'filament.directivo.pages.revisar-convocatoria';

    protected static ?string $slug =
        'revisar-convocatoria/{record}';

    protected static bool $shouldRegisterNavigation =
        false;

    protected static ?string $title =
        'Revisar Convocatoria';

    public Convocatoria $convocatoria;

    public static function getRelativeRouteName(
        Panel $panel
    ): string {
        return 'revisar-convocatoria';
    }

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'DIRECTIVO'
            );
    }

    public function mount(
        string|int $record
    ): void {
        $this->convocatoria =
            Convocatoria::query()
                ->with([
                    'categoria',
                    'organismo',
                    'fuente',
                    'requisitos',
                    'archivos',
                    'revisiones',
                    'revisionesAdministrativas.revisor',
                ])
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->findOrFail(
                    $record
                );
    }

    public function getTitle(): string
    {
        return 'Revisar Convocatoria';
    }

    protected function getViewData(): array
    {
        $convocatoria =
            $this->convocatoria;

        $fechaDetectada =
            $convocatoria->fecha_extraccion
            ?? $convocatoria->created_at;

        $fechaApertura =
            $convocatoria->fecha_inicio
            ?? $convocatoria->fecha_publicacion;

        $origenTexto =
            match (
                $convocatoria->origen
            ) {
                'SCRAPING' =>
                    'Extracción automática',

                'MANUAL' =>
                    'Registro manual',

                'API' =>
                    'Registro mediante API',

                default =>
                    $convocatoria->origen
                    ?: 'No especificado',
            };

        $documentos =
            $convocatoria
                ->archivos
                ->map(
                    function (
                        $archivo
                    ): array {
                        $nombre =
                            trim(
                                (string)
                                $archivo->getAttribute(
                                    'nombre'
                                )
                            );

                        $tipo =
                            trim(
                                (string)
                                $archivo->getAttribute(
                                    'tipo_archivo'
                                )
                            );

                        $mime =
                            trim(
                                (string)
                                $archivo->getAttribute(
                                    'mime_type'
                                )
                            );

                        return [
                            'id' =>
                                $archivo->id,

                            'nombre' =>
                                $nombre !== ''
                                    ? $nombre
                                    : 'Documento',

                            'tipo' =>
                                $tipo !== ''
                                    ? $tipo
                                    : (
                                        $mime !== ''
                                            ? $mime
                                            : 'Archivo'
                                    ),

                            'url' =>
                                $this->urlHttpSegura(
                                    $archivo->getAttribute(
                                        'url_archivo'
                                    )
                                ),
                        ];
                    }
                );

        $cambios =
            $convocatoria
                ->revisiones
                ->map(
                    function (
                        $revision
                    ): array {
                        $tipo =
                            trim(
                                (string)
                                $revision->tipo_revision
                            );

                        $campo =
                            match ($tipo) {
                                'FECHA_CIERRE' =>
                                    'Fecha de cierre',

                                default =>
                                    $tipo !== ''
                                        ? Str::headline(
                                            strtolower(
                                                $tipo
                                            )
                                        )
                                        : 'Cambio detectado',
                            };

                        return [
                            'id' =>
                                $revision->id,

                            'campo' =>
                                $campo,

                            'anterior' =>
                                $revision
                                    ->fecha_cierre_anterior
                                    ?->format(
                                        'd/m/Y'
                                    )
                                ?? 'No registrado',

                            'nuevo' =>
                                $revision
                                    ->fecha_cierre_nueva
                                    ?->format(
                                        'd/m/Y'
                                    )
                                ?? 'No registrado',

                            'fuente' =>
                                $revision->fuente_tipo
                                ?: 'No especificada',

                            'fuente_url' =>
                                $this->urlHttpSegura(
                                    $revision->fuente_url
                                ),

                            'observaciones' =>
                                $revision->observaciones,

                            'requiere_verificacion' =>
                                (bool)
                                $revision
                                    ->requiere_verificacion,

                            'fecha_revision' =>
                                $revision->fecha_revision,
                        ];
                    }
                );

        $tieneFechas =
            $convocatoria->fecha_publicacion
            || $convocatoria->fecha_inicio
            || $convocatoria->fecha_cierre;

        $tieneMonto =
            $convocatoria->monto_minimo !== null
            || $convocatoria->monto_maximo !== null;

        $verificaciones = [
            [
                'texto' =>
                    'Título identificado',
                'ok' =>
                    trim(
                        (string)
                        $convocatoria->titulo
                    ) !== '',
            ],
            [
                'texto' =>
                    'Organismo identificado',
                'ok' =>
                    $convocatoria->organismo
                    !== null,
            ],
            [
                'texto' =>
                    'Fechas identificadas',
                'ok' =>
                    (bool) $tieneFechas,
            ],
            [
                'texto' =>
                    'Monto identificado',
                'ok' =>
                    $tieneMonto,
            ],
            [
                'texto' =>
                    'Requisitos identificados',
                'ok' =>
                    $convocatoria
                        ->requisitos
                        ->isNotEmpty(),
            ],
            [
                'texto' =>
                    'Documentos encontrados',
                'ok' =>
                    $convocatoria
                        ->archivos
                        ->isNotEmpty(),
            ],
        ];

        return [
            'fechaDetectada' =>
                $fechaDetectada,

            'fechaApertura' =>
                $fechaApertura,

            'origenTexto' =>
                $origenTexto,

            'documentos' =>
                $documentos,

            'cambios' =>
                $cambios,

            'verificaciones' =>
                $verificaciones,

            'urlOficial' =>
                $this->urlHttpSegura(
                    $convocatoria->url_original
                ),
        ];
    }

    private function urlHttpSegura(
        mixed $url
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
