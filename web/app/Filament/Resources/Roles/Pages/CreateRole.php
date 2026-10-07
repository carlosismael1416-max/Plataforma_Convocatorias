<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource =
        RoleResource::class;

    public function getTitle(): string
    {
        return 'Registrar rol';
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rol registrado correctamente';
    }
}
