<?php

namespace App\Filament\Resources\DescubrimientoWebs\Pages;

use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDescubrimientoWebs extends ListRecords
{
    protected static string $resource = DescubrimientoWebResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
