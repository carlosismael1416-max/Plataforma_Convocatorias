<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Str;

class DetalleConvocatoria extends Page
{
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
        $this->actualizarSeguimiento();
    }

    /**
     * Como el docente, el directivo puede guardar convocatorias publicadas y
     * vigentes en Mis convocatorias.
     */
    public function guardarConvocatoria(): void
    {
        $convocatoria = $this->consultaDisponibles()
            ->whereKey($this->convocatoria->id)
            ->first();

        if (! $convocatoria) {
            Notification::make()
                ->title('Convocatoria no disponible')
                ->body('Solo se pueden guardar convocatorias publicadas y vigentes.')
                ->warning()
                ->send();

            return;
        }

        $seguimiento = UsuarioConvocatoria::query()->firstOrNew([
            'user_id' => auth()->id(),
            'convocatoria_id' => $convocatoria->id,
        ]);

        if (! $seguimiento->exists) {
            $seguimiento->estado_seguimiento = 'GUARDADA';
            $seguimiento->prioridad = 'MEDIA';
            $seguimiento->es_favorita = false;
        }

        $seguimiento->ultima_accion = 'Agregada desde Detalle de Convocatoria';
        $seguimiento->fecha_ultima_accion = now();
        $seguimiento->save();

        $this->actualizarSeguimiento();

        Notification::make()
            ->title('Convocatoria guardada')
            ->body('Se agregó a Mis convocatorias.')
            ->success()
            ->send();
    }

    private function consultaDisponibles()
    {
        return Convocatoria::query()
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
            ->noVencidas();
    }

    private function actualizarSeguimiento(): void
    {
        $this->disponible = $this->consultaDisponibles()
            ->whereKey($this->convocatoria->id)
            ->exists();

        $this->guardada = UsuarioConvocatoria::query()
            ->where('user_id', auth()->id())
            ->where('convocatoria_id', $this->convocatoria->id)
            ->exists();

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
