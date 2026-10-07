<?php

namespace App\Filament\Resources\Propuestas\Pages;

use App\Filament\Resources\Propuestas\PropuestaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPropuesta extends EditRecord
{
    protected static string $resource = PropuestaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
