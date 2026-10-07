<?php

namespace App\Filament\Resources\Propuestas\Pages;

use App\Filament\Resources\Propuestas\PropuestaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPropuesta extends ViewRecord
{
    protected static string $resource = PropuestaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
