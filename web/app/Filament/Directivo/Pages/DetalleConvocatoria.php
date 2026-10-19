<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Concerns\GestionaMisConvocatorias;
use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Str;

class DetalleConvocatoria extends Page
{
    use GestionaMisConvocatorias;

    protected string $view =
        'filament.directivo.pages.detalle-convocatoria';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    protected static ?string $slug =
        'detalle-convocatoria/{record}';

    protected static bool $shouldRegisterNavigation =
        false;

    protected static ?string $title =
        'Detalle de Convocatoria';

    public Convocatoria $convocatoria;

    // ¿Puede guardarla y preparar una propuesta? (publicada y vigente)
    public bool $disponible = false;

    public bool $guardada = false;

    public bool $favorita = false;

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
                'seguimiento',
                    'requisitos',
                    'archivos',
                ])
                // Solo lo que ven todos; lo no publicado es interno del motor.
                ->whereIn('estado', BuscarConvocatorias::ESTADOS_VISIBLES)
                ->findOrFail(
                    $record
                );
        $this->despuesDeCambiarSeguimiento();
    }

    /** Compatibilidad con la vista anterior. */
    public function guardarConvocatoria(): void
    {
        $this->guardarEnMis($this->convocatoria->id);
    }

    protected function despuesDeCambiarSeguimiento(): void
    {
        $this->disponible = Convocatoria::query()
            ->visibles()
            ->whereKey($this->convocatoria->id)
            ->exists();

        $seguimiento = UsuarioConvocatoria::query()
            ->where('user_id', auth()->id())
            ->where('convocatoria_id', $this->convocatoria->id)
            ->first();

        $this->guardada = $seguimiento !== null;
        $this->favorita = (bool) $seguimiento?->es_favorita;

        $this->tienePropuesta = Propuesta::query()
            ->where('user_id', auth()->id())
            ->where('convocatoria_id', $this->convocatoria->id)
            ->exists();
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
