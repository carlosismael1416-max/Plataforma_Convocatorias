<?php

namespace App\Filament\Resources\Organismos\Pages;

use App\Filament\Resources\Organismos\OrganismoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrganismo extends ViewRecord
{
    protected static string $resource = OrganismoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
