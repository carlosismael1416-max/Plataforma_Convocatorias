<?php

namespace App\Filament\Resources\Fuentes\Pages;

use App\Filament\Admin\Pages\GestionarFuente;
use App\Filament\Resources\Fuentes\FuenteResource;
use App\Models\EjecucionFuente;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditFuente extends EditRecord
{
    protected static string $resource = FuenteResource::class;

    public function getTitle(): string
    {
        return 'Editar fuente';
    }

    public function getSubheading(): ?string
    {
        return $this->getRecord()->nombre;
    }

    /** Volver, guardar y cancelar llevan a «Gestionar fuente», no al detalle genérico. */
    private function urlGestionar(): string
    {
        return GestionarFuente::getUrl(['record' => $this->getRecord()->id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gestionar')
                ->label('Gestionar fuente')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->outlined()
                ->url($this->urlGestionar()),

            // Con ejecuciones registradas la base no permite borrarla
            // (ejecucion_fuentes la restringe): se desactiva en su lugar.
            DeleteAction::make()
                ->label('Eliminar')
                ->outlined()
                ->visible(fn (): bool => ! EjecucionFuente::query()->where('fuente_id', $this->getRecord()->id)->exists())
                ->modalHeading('Eliminar fuente')
                ->modalDescription('Se borra la fuente; sus convocatorias se conservan. Si solo quieres dejar de consultarla, mejor desactívala.'),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url($this->urlGestionar());
    }

    protected function getRedirectUrl(): string
    {
        return $this->urlGestionar();
    }
}
