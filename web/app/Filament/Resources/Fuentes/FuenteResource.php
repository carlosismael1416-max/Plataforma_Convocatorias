<?php

namespace App\Filament\Resources\Fuentes;

use App\Filament\Resources\Fuentes\Pages\CreateFuente;
use App\Filament\Resources\Fuentes\Pages\EditFuente;
use App\Filament\Resources\Fuentes\Pages\ListFuentes;
use App\Filament\Resources\Fuentes\Pages\ViewFuente;
use App\Filament\Resources\Fuentes\Schemas\FuenteForm;
use App\Filament\Resources\Fuentes\Schemas\FuenteInfolist;
use App\Filament\Resources\Fuentes\Tables\FuentesTable;
use App\Models\Fuente;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FuenteResource extends Resource
{
    protected static ?string $model =
        Fuente::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel =
        'Fuentes web';

    protected static ?string $modelLabel =
        'fuente web';

    protected static ?string $pluralModelLabel =
        'Fuentes web';

    protected static ?int $navigationSort =
        4;

    protected static ?string $recordTitleAttribute =
        'nombre';

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

    public static function form(
        Schema $schema
    ): Schema {
        return FuenteForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return FuenteInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return FuentesTable::configure(
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
                ListFuentes::route('/'),

            'create' =>
                CreateFuente::route(
                    '/create'
                ),

            'view' =>
                ViewFuente::route(
                    '/{record}'
                ),

            'edit' =>
                EditFuente::route(
                    '/{record}/edit'
                ),
        ];
    }
}
