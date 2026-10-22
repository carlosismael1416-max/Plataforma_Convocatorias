<?php

namespace App\Filament\Resources\Organismos\Pages;

use App\Filament\Resources\Organismos\OrganismoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganismos extends ListRecords
{
    protected static string $resource = OrganismoResource::class;

    protected string $view = 'filament.admin.catalogos.list-organismos';

    protected static ?string $title = 'Organismos';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo organismo'),
        ];
    }
}
