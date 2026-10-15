<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected string $view = 'filament.admin.roles.list-roles';

    // La vista ya trae su encabezado.
    protected ?string $heading = '';

    protected function getViewData(): array
    {
        return [
            'totalRoles' => Role::query()->count(),
            'rolesActivos' => Role::query()->where('estado', true)->count(),
            'usuariosConRol' => User::query()->whereNotNull('role_id')->count(),
            'totalPermisos' => Permiso::query()->count(),
            'roles' => Role::query()
                ->with('permisos:id,nombre,descripcion')
                ->withCount('users')
                ->orderBy('id')
                ->get(),
        ];
    }

    public function getTitle(): string
    {
        return 'Roles y Permisos';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo rol'),
        ];
    }
}
