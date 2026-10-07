<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Str;

class DetalleConvocatoria extends Page
{
    protected string $view =
        'filament.directivo.pages.detalle-convocatoria';

    protected static ?string $slug =
        'detalle-convocatoria/{record}';

    protected static bool $shouldRegisterNavigation =
        false;

    protected static ?string $title =
        'Detalle de Convocatoria';

    public Convocatoria $convocatoria;

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
                ])
                ->findOrFail(
                    $record
                );
    }

    public function getTitle(): string
    {
        return 'Detalle de Convocatoria';
    }

    protected function getViewData(): array
    {
        $convocatoria =
            $this->convocatoria;

        $fechaApertura =
            $convocatoria->fecha_inicio
            ?? $convocatoria->fecha_publicacion;

        $fechaCierre =
            $convocatoria->fecha_cierre;

        $urlOficial =
            $this->urlHttpSegura(
                $convocatoria->url_original
            );

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

                        $url =
                            $this->urlHttpSegura(
                                $archivo->getAttribute(
                                    'url_archivo'
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
                                $url,
                        ];
                    }
                );

        return [
            'estadoTexto' =>
                $this->etiquetaEstado(
                    $convocatoria->estado
                ),

            'estadoClase' =>
                $this->claseEstado(
                    $convocatoria->estado
                ),

            'fechaApertura' =>
                $fechaApertura,

            'fechaCierre' =>
                $fechaCierre,

            'urlOficial' =>
                $urlOficial,

            'documentos' =>
                $documentos,

            'descripcionCorta' =>
                Str::limit(
                    trim(
                        (string)
                        (
                            $convocatoria
                                ->descripcion
                            ?: $convocatoria
                                ->objetivo
                        )
                    ),
                    1000
                ),
        ];
    }

    private function etiquetaEstado(
        ?string $estado
    ): string {
        return match ($estado) {
            'BORRADOR' =>
                'Borrador',

            'PENDIENTE_REVISION' =>
                'Pendiente de revisión',

            'REQUIERE_CORRECCIONES' =>
                'Requiere correcciones',

            'PUBLICADA' =>
                'Publicada',

            'ACTIVA' =>
                'Activa',

            'CERRADA' =>
                'Cerrada',

            'DESCARTADA' =>
                'Descartada',

            'ARCHIVADA' =>
                'Archivada',

            default =>
                $estado
                ?: 'Sin estado',
        };
    }

    private function claseEstado(
        ?string $estado
    ): string {
        return match ($estado) {
            'PUBLICADA',
            'ACTIVA' =>
                'status-open',

            'PENDIENTE_REVISION' =>
                'status-review',

            'REQUIERE_CORRECCIONES' =>
                'status-correction',

            'DESCARTADA' =>
                'status-danger',

            default =>
                'status-neutral',
        };
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
