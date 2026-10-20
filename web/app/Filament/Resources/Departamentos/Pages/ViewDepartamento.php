<?php

namespace App\Filament\Resources\Departamentos\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\Departamentos\DepartamentoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDepartamento extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = DepartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            EditAction::make(),
        ];
    }
}
