<?php

namespace App\Filament\Resources\DescubrimientoWebs\Pages;

use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDescubrimientoWeb extends ViewRecord
{
    protected static string $resource = DescubrimientoWebResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
