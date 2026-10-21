<?php

namespace App\Filament\Resources\Convocatorias\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

// Se publican solas (motor validado o subida del personal); las que el
// validador no publicó las revisa el administrador en RevisarConvocatoria.
class ViewConvocatoria extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            \Filament\Actions\Action::make('revisar')
                ->label('Revisar y decidir')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->visible(fn (): bool => in_array($this->getRecord()->estado, \App\Services\RevisionConvocatoria::REVISABLES, true))
                ->url(fn (): string => \App\Filament\Admin\Pages\RevisarConvocatoria::getUrl(['record' => $this->getRecord()->id])),

            EditAction::make(),
        ];
    }
}
