<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Docente\Pages\VistaPreviaPropuesta;
use App\Models\Propuesta;
use App\Services\RevisionPropuesta;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Revisión de una propuesta enviada (de un docente o del propio directivo):
 * la vista previa en solo lectura más el dictamen.
 */
class RevisarPropuesta extends VistaPreviaPropuesta
{
    protected static ?string $title = 'Revisar propuesta';

    protected static ?string $slug = 'revisar-propuesta';

    public string $observaciones = '';

    protected function consultaPropuestas(): Builder
    {
        return Propuesta::query()
            ->whereIn('estado', ['ENVIADA', 'APROBADA', 'RECHAZADA']);
    }

    protected function esModoRevision(): bool
    {
        return true;
    }

    public function enviarARevision(): void
    {
        abort(403);
    }

    public function aprobar(): mixed
    {
        return $this->dictaminar('APROBAR', 'Propuesta aprobada');
    }

    public function devolver(): mixed
    {
        return $this->dictaminar('DEVOLVER', 'Propuesta devuelta al autor');
    }

    public function rechazar(): mixed
    {
        return $this->dictaminar('RECHAZAR', 'Propuesta rechazada');
    }

    private function dictaminar(string $decision, string $mensaje): mixed
    {
        $this->validate(['observaciones' => ['nullable', 'string', 'max:4000']]);

        $propuesta = $this->consultaPropuestas()
            ->whereKey($this->propuestaId)
            ->firstOrFail();

        try {
            RevisionPropuesta::dictaminar($propuesta, auth()->user(), $decision, $this->observaciones);
        } catch (ValidationException $e) {
            Notification::make()
                ->title('No se pudo registrar el dictamen')
                ->body(collect($e->errors())->flatten()->first())
                ->warning()
                ->send();

            return null;
        }

        Notification::make()
            ->title($mensaje)
            ->body('El autor recibió una notificación.')
            ->success()
            ->send();

        return redirect(PropuestasPorRevisar::getUrl());
    }
}
