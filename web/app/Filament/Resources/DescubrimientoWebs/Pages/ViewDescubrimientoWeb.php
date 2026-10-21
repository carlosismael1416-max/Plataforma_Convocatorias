<?php

namespace App\Filament\Resources\DescubrimientoWebs\Pages;

use App\Filament\Admin\Pages\RevisarConvocatoria;
use App\Filament\Concerns\BotonVolver;
use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use App\Models\Convocatoria;
use App\Models\User;
use App\Services\RegistroActividad;
use App\Services\RevisionConvocatoria;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ViewDescubrimientoWeb extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = DescubrimientoWebResource::class;

    /** Último estado mostrado (Livewire recarga el registro en cada petición). */
    public ?string $estadoVisto = null;

    /** Estados desde los que «Extraer ahora» no aplica. */
    private const NO_PROCESABLES = ['EN_PROCESO', 'PROCESADO'];

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            Action::make('extraerAhora')
                ->label('Extraer ahora')
                ->icon(Heroicon::OutlinedBolt)
                ->color('primary')
                ->visible(fn (): bool => $this->puedeExtraer())
                ->requiresConfirmation()
                ->modalHeading('Extraer este descubrimiento ahora')
                ->modalDescription('El motor lo procesa ya, sin esperar al ciclo automático: descarga la página y sus PDF, extrae los datos y los valida con los mismos criterios de siempre. Si el validador lo aprueba se publica solo; si no, podrás revisarlo y decidir. Puede tardar unos minutos.')
                ->modalSubmitActionLabel('Extraer ahora')
                ->action(fn () => $this->extraerAhora()),

            Action::make('revisarConvocatoria')
                ->label('Revisar y decidir publicación')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('warning')
                ->visible(fn (): bool => $this->convocatoria() !== null
                    && in_array($this->convocatoria()->estado, RevisionConvocatoria::REVISABLES, true)
                    && RevisionConvocatoria::puedeRevisar(auth()->user()))
                ->url(fn (): string => RevisarConvocatoria::getUrl(['record' => $this->getRecord()->convocatoria_id])),

            Action::make('verConvocatoria')
                ->label('Ver convocatoria')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->outlined()
                ->visible(fn (): bool => $this->convocatoria() !== null)
                ->url(fn (): string => ConvocatoriaResource::getUrl('view', ['record' => $this->getRecord()->convocatoria_id])),
        ];
    }

    /** Mientras se extrae, la vista se refresca sola (wire:poll en la vista). */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->estadoVisto = $this->getRecord()->estado;
    }

    public function refrescarEstado(): void
    {
        $this->getRecord()->refresh();
        $antes = $this->estadoVisto;
        $this->estadoVisto = $this->getRecord()->estado;

        if ($antes === 'EN_PROCESO' && $this->estadoVisto !== 'EN_PROCESO') {
            $convocatoria = $this->convocatoria();

            Notification::make()
                ->title('Extracción terminada')
                ->body(match (true) {
                    $convocatoria?->estado === 'PUBLICADA' => 'El validador la aprobó y ya está publicada.',
                    $convocatoria !== null => 'Se creó la convocatoria, pero el validador no la publicó. Revísala y decide.',
                    default => 'No se obtuvo una convocatoria. Revisa «Último error».',
                })
                ->success()
                ->send();
        }
    }

    public function extraerEnCurso(): bool
    {
        return $this->getRecord()->estado === 'EN_PROCESO';
    }

    private function puedeExtraer(): bool
    {
        $usuario = auth()->user();
        $registro = $this->getRecord();

        return $usuario instanceof User
            && $usuario->tieneRol('ADMINISTRADOR')
            && $usuario->tienePermiso('ejecutar_scraping')
            && $registro->convocatoria_id === null
            && ! in_array($registro->estado, self::NO_PROCESABLES, true);
    }

    private function extraerAhora(): void
    {
        abort_unless($this->puedeExtraer(), 403);

        $registro = $this->getRecord();

        try {
            $respuesta = Http::acceptJson()
                ->timeout(20)
                ->post(rtrim((string) config('services.extractor.url'), '/') . "/descubrimientos/{$registro->id}/procesar");
        } catch (ConnectionException) {
            Notification::make()->title('El motor de extracción no responde')->body('Revisa en Motor de extracción que la API esté encendida.')->danger()->send();

            return;
        }

        if (! $respuesta->successful()) {
            Notification::make()
                ->title('No se pudo extraer ahora')
                ->body((string) ($respuesta->json('detail') ?? 'Inténtalo más tarde.'))
                ->warning()
                ->send();

            return;
        }

        RegistroActividad::registrar('MOTOR', 'Extrajo a mano el descubrimiento #' . $registro->id, $registro);

        // El proceso marca EN_PROCESO en cuanto empieza; se espera un momento
        // para que la vista ya muestre «extrayendo».
        usleep(1_500_000);
        $registro->refresh();
        $this->estadoVisto = $registro->estado;

        Notification::make()
            ->title('Extracción iniciada')
            ->body('Esta página se actualiza sola mientras el motor trabaja; puede tardar unos minutos.')
            ->success()
            ->send();
    }

    private function convocatoria(): ?Convocatoria
    {
        $id = $this->getRecord()->convocatoria_id;

        return $id ? Convocatoria::query()->find($id) : null;
    }
}
