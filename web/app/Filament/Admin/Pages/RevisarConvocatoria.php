<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use App\Models\User;
use App\Services\RevisionConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Locked;

/**
 * Revisión manual de una convocatoria que el validador no publicó o que se
 * descartó: el administrador la vuelve a pasar por el validador del motor,
 * ve los motivos y decide publicarla o descartarla.
 */
class RevisarConvocatoria extends Page
{
    protected string $view = 'filament.admin.pages.revisar-convocatoria';

    protected ?string $heading = '';

    protected static ?string $title = 'Revisar convocatoria';

    protected static ?string $slug = 'revisar-convocatoria/{record}';

    protected static bool $shouldRegisterNavigation = false;

    #[Locked]
    public int $record;

    public string $tipo = '';

    public string $observaciones = '';

    /** Resultado del validador del motor (no se guarda). */
    public ?array $validacion = null;

    public ?array $datosMotor = null;

    public static function canAccess(): bool
    {
        return RevisionConvocatoria::puedeRevisar(auth()->user());
    }

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'revisar-convocatoria';
    }

    public function mount(int|string $record): void
    {
        $convocatoria = Convocatoria::query()->findOrFail($record);
        $this->record = $convocatoria->id;
        $this->tipo = isset(Convocatoria::TIPOS[$convocatoria->tipo ?? '']) ? $convocatoria->tipo : '';
    }

    public function getTitle(): string
    {
        return 'Revisar convocatoria';
    }

    public function convocatoria(): Convocatoria
    {
        return Convocatoria::query()->with(['organismo', 'fuente', 'categoria'])->findOrFail($this->record);
    }

    /** Vuelve a pasar el enlace por el validador del motor (no guarda nada). */
    public function validarAhora(): void
    {
        $convocatoria = $this->convocatoria();

        if (! $convocatoria->url_original) {
            $this->avisar('No tiene enlace', 'Sin enlace no se puede volver a validar; decide con los datos que tiene.');

            return;
        }

        try {
            $respuesta = Http::acceptJson()
                ->timeout(120)
                ->post(rtrim((string) config('services.extractor.url'), '/') . '/convocatorias/analizar-url', [
                    'url' => $convocatoria->url_original,
                ]);
        } catch (ConnectionException) {
            $this->avisar('El motor de extracción no responde', 'Puedes decidir con los datos que ya tiene la convocatoria.');

            return;
        }

        if (! $respuesta->successful()) {
            $this->avisar(
                match ($respuesta->status()) {
                    409 => 'El motor de extracción está apagado',
                    422 => 'El enlace no se pudo usar',
                    429 => 'Hay otro enlace en análisis; intenta en un momento',
                    default => 'No se pudo validar',
                },
                (string) ($respuesta->json('detail') ?? 'Puedes decidir con los datos que ya tiene la convocatoria.')
            );

            return;
        }

        $this->validacion = $respuesta->json('validacion');
        $this->datosMotor = $respuesta->json('datos');

        if ($this->tipo === '' && isset(Convocatoria::TIPOS[$this->datosMotor['tipo'] ?? ''])) {
            $this->tipo = $this->datosMotor['tipo'];
        }
    }

    public function publicar(): mixed
    {
        $this->resetErrorBag();

        RevisionConvocatoria::publicar($this->convocatoria(), $this->usuario(), $this->tipo, $this->observaciones);

        Notification::make()->title('Convocatoria publicada')->body('Ya la ven docentes y directivos.')->success()->send();

        return redirect(ConvocatoriaResource::getUrl('view', ['record' => $this->record]));
    }

    public function descartar(): mixed
    {
        $this->resetErrorBag();

        RevisionConvocatoria::descartar($this->convocatoria(), $this->usuario(), $this->observaciones);

        Notification::make()->title('Convocatoria descartada')->body('El motor ya no la volverá a publicar.')->success()->send();

        return redirect(ConvocatoriaResource::getUrl('index', ['tab' => 'descartadas']));
    }

    public function devolver(): void
    {
        $this->resetErrorBag();

        RevisionConvocatoria::devolver($this->convocatoria(), $this->usuario(), $this->observaciones);

        $this->observaciones = '';
        Notification::make()->title('Devuelta a «no publicadas»')->body('El motor volverá a revalidarla en sus ciclos.')->success()->send();
    }

    protected function getViewData(): array
    {
        $convocatoria = $this->convocatoria();

        return [
            'convocatoria' => $convocatoria,
            'revisable' => in_array($convocatoria->estado, RevisionConvocatoria::REVISABLES, true),
            'impedimentos' => RevisionConvocatoria::impedimentos($convocatoria, $this->tipo ?: null),
            'decisiones' => $convocatoria->revisionesAdministrativas()->with('revisor:id,name,apellidos')->orderByDesc('fecha_decision')->get(),
            'urlEditar' => ConvocatoriaResource::getUrl('edit', ['record' => $convocatoria]),
            'urlDetalle' => ConvocatoriaResource::getUrl('view', ['record' => $convocatoria]),
            'urlLista' => ConvocatoriaResource::getUrl('index'),
        ];
    }

    private function usuario(): User
    {
        return User::query()->findOrFail(auth()->id());
    }

    private function avisar(string $titulo, string $cuerpo): void
    {
        Notification::make()->title($titulo)->body($cuerpo)->warning()->send();
    }
}
