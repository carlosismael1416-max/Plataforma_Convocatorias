<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Filament\Resources\Roles\Schemas\RoleInfolist;
use App\Filament\Resources\Roles\Tables\RolesTable;
use App\Models\Role;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    protected static ?string $model =
        Role::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel =
        'Roles y permisos';

    protected static ?string $modelLabel =
        'rol';

    protected static ?string $pluralModelLabel =
        'Roles y permisos';

    protected static ?int $navigationSort =
        3;

    protected static ?string $recordTitleAttribute =
        'nombre';

    private const ROLES_BASE = [
        'DOCENTE',
        'DIRECTIVO',
        'ADMINISTRADOR',
        'SISTEMA',
    ];

    private static function esAdministradorActivo(): bool
    {
        $usuario =
            auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'users',
                'permisos',
            ]);
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
        if (
            ! static::esAdministradorActivo()
        ) {
            return false;
        }

        if (
            in_array(
                $record->nombre,
                self::ROLES_BASE,
                true
            )
        ) {
            return false;
        }

        return ! $record
            ->users()
            ->exists();
    }

    public static function esRolBase(
        Role $role
    ): bool {
        return in_array(
            $role->nombre,
            self::ROLES_BASE,
            true
        );
    }

    public static function form(
        Schema $schema
    ): Schema {
        return RoleForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return RoleInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return RolesTable::configure(
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
                ListRoles::route('/'),

            'create' =>
                CreateRole::route(
                    '/create'
                ),

            'view' =>
                ViewRole::route(
                    '/{record}'
                ),

            'edit' =>
                EditRole::route(
                    '/{record}/edit'
                ),
        ];
    }
}
