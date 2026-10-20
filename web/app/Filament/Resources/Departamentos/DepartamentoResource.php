<?php

namespace App\Filament\Resources\Departamentos;

use App\Filament\Resources\Departamentos\Pages\CreateDepartamento;
use App\Filament\Resources\Departamentos\Pages\EditDepartamento;
use App\Filament\Resources\Departamentos\Pages\ListDepartamentos;
use App\Filament\Resources\Departamentos\Pages\ViewDepartamento;
use App\Filament\Resources\Departamentos\Schemas\DepartamentoForm;
use App\Filament\Resources\Departamentos\Schemas\DepartamentoInfolist;
use App\Filament\Resources\Departamentos\Tables\DepartamentosTable;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DepartamentoResource extends Resource
{
    // Pestaña de «Usuarios» (no está en el menú).
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'departamento';

    protected static ?string $pluralModelLabel = 'departamentos';

    private static function esAdministradorActivo(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User && (bool) $usuario->estado && $usuario->tieneRol('ADMINISTRADOR');
    }

    public static function canViewAny(): bool
    {
        return static::esAdministradorActivo();
    }

    public static function canCreate(): bool
    {
        return static::esAdministradorActivo();
    }

    public static function canView(Model $record): bool
    {
        return static::esAdministradorActivo();
    }

    public static function canEdit(Model $record): bool
    {
        return static::esAdministradorActivo();
    }

    /** Solo se elimina si ningún usuario lo tiene asignado. */
    public static function canDelete(Model $record): bool
    {
        return static::esAdministradorActivo() && ! $record->users()->exists();
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    protected static ?string $model = Departamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return DepartamentoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DepartamentoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartamentosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartamentos::route('/'),
            'create' => CreateDepartamento::route('/create'),
            'view' => ViewDepartamento::route('/{record}'),
            'edit' => EditDepartamento::route('/{record}/edit'),
        ];
    }
}
