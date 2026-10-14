<?php

namespace App\Filament\Resources\DescubrimientoWebs\Pages;

use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDescubrimientoWeb extends EditRecord
{
    protected static string $resource = DescubrimientoWebResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
