<?php

namespace App\Filament\Resources\Organismos\Pages;

use App\Filament\Resources\Organismos\OrganismoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganismos extends ListRecords
{
    protected static string $resource = OrganismoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
