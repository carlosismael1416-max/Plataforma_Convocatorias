<?php

namespace App\Filament\Resources\Categorias\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\Categorias\CategoriaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoria extends ViewRecord
{
    use BotonVolver;

    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            EditAction::make(),
        ];
    }
}