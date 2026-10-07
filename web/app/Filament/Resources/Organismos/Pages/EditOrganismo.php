<?php

namespace App\Filament\Resources\Organismos\Pages;

use App\Filament\Resources\Organismos\OrganismoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganismo extends EditRecord
{
    protected static string $resource = OrganismoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
