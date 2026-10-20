<?php

namespace App\Filament\Resources\DescubrimientoWebs\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDescubrimientoWeb extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = DescubrimientoWebResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),
        ];
    }
}
