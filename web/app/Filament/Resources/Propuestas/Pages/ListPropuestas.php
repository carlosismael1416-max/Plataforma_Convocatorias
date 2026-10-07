<?php

namespace App\Filament\Resources\Propuestas\Pages;

use App\Filament\Resources\Propuestas\PropuestaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropuestas extends ListRecords
{
    protected static string $resource = PropuestaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
