<?php

namespace App\Filament\Docente\Resources\Convocatorias\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Docente\Resources\Convocatorias\ConvocatoriaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConvocatoria extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            EditAction::make()
                ->visible(
                    fn (): bool =>
                        ConvocatoriaResource::canEdit(
                            $this->getRecord()
                        )
                ),
        ];
    }
}
