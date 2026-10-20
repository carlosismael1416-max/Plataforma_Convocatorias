<?php

namespace App\Filament\Resources\Departamentos\Pages;

use App\Filament\Resources\Departamentos\DepartamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/** Pestaña «Departamentos» de Usuarios. */
class ListDepartamentos extends ListRecords
{
    protected static string $resource = DepartamentoResource::class;

    protected string $view = 'filament.admin.departamentos.list-departamentos';

    protected static ?string $title = 'Departamentos';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo departamento')->icon('heroicon-o-plus'),
        ];
    }
}
