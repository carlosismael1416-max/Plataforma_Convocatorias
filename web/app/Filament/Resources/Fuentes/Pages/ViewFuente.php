<?php

namespace App\Filament\Resources\Fuentes\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\Fuentes\FuenteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFuente extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = FuenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            EditAction::make(),
        ];
    }
}
