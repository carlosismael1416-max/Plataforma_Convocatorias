<?php

namespace App\Filament\Docente\Resources\Convocatorias\Pages;

use App\Filament\Docente\Resources\Convocatorias\ConvocatoriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConvocatorias extends ListRecords
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Subir convocatoria'),
        ];
    }
}
