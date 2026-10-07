<?php

namespace App\Filament\Resources\Fuentes\Pages;

use App\Filament\Resources\Fuentes\FuenteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFuente extends EditRecord
{
    protected static string $resource = FuenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
