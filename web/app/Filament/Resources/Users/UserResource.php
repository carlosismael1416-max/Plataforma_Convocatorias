<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel =
        'Usuarios';

    protected static ?string $modelLabel =
        'usuario';

    protected static ?string $pluralModelLabel =
        'Usuarios';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute =
        'name';

    private static function esAdministradorActivo(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    public static function canViewAny(): bool
    {
        return static::esAdministradorActivo();
    }

    public static function canCreate(): bool
    {
        return static::esAdministradorActivo();
    }

    public static function canView(
        Model $record
    ): bool {
        return static::esAdministradorActivo();
    }

    public static function canEdit(
        Model $record
    ): bool {
        return static::esAdministradorActivo();
    }

    public static function canDelete(
        Model $record
    ): bool {
        return static::esAdministradorActivo()
            && (int) $record->getKey()
                !== (int) auth()->id();
    }

    public static function form(
        Schema $schema
    ): Schema {
        return UserForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return UserInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return UsersTable::configure(
            $table
        );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListUsers::route('/'),

            'create' =>
                CreateUser::route('/create'),

            'view' =>
                ViewUser::route('/{record}'),

            'edit' =>
                EditUser::route(
                    '/{record}/edit'
                ),
        ];
    }
}
