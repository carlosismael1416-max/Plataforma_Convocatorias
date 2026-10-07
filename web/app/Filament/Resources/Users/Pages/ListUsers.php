<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource =
        UserResource::class;

    protected string $view =
        'filament.resources.users.pages.list-users';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo usuario')
                ->icon('heroicon-o-user-plus'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'totalUsuarios' =>
                User::query()->count(),

            'totalDocentes' =>
                User::query()
                    ->whereHas(
                        'role',
                        fn ($query) =>
                            $query->where(
                                'nombre',
                                'DOCENTE'
                            )
                    )
                    ->count(),

            'totalDirectivos' =>
                User::query()
                    ->whereHas(
                        'role',
                        fn ($query) =>
                            $query->where(
                                'nombre',
                                'DIRECTIVO'
                            )
                    )
                    ->count(),

            'totalAdministradores' =>
                User::query()
                    ->whereHas(
                        'role',
                        fn ($query) =>
                            $query->where(
                                'nombre',
                                'ADMINISTRADOR'
                            )
                    )
                    ->count(),
        ];
    }
}
