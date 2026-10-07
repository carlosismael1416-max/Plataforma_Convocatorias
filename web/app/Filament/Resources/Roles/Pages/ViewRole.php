<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    protected static string $resource =
        RoleResource::class;

    public function getTitle(): string
    {
        return 'Detalle del rol';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(
                    'Editar rol y permisos'
                ),
        ];
    }
}
