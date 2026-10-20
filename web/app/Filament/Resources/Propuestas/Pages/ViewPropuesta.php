<?php

namespace App\Filament\Resources\Propuestas\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\Propuestas\PropuestaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPropuesta extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = PropuestaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            EditAction::make(),
        ];
    }
}
