<?php

namespace App\Filament\Resources\Convocatorias\Pages;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

// El administrador ya no aprueba ni solicita correcciones de convocatorias;
// se publican solas (motor validado o subida del personal).
class ViewConvocatoria extends ViewRecord
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
