<?php

namespace App\Filament\Resources\Categorias;

use App\Filament\Resources\Categorias\Pages\CreateCategoria;
use App\Filament\Resources\Categorias\Pages\EditCategoria;
use App\Filament\Resources\Categorias\Pages\ListCategorias;
use App\Filament\Resources\Categorias\Schemas\CategoriaForm;
use App\Filament\Resources\Categorias\Tables\CategoriasTable;
use App\Models\Categoria;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Catálogo de categoría: pestaña de Convocatorias en el admin (fuera del menú). */
class CategoriaResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Categoria::class;

    protected static ?string $modelLabel = 'categoría';

    protected static ?string $pluralModelLabel = 'categorías';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

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

    public static function canEdit(Model $record): bool
    {
        return static::esAdministradorActivo();
    }

    // Borrar dejaría convocatorias sin categoría; se desactiva en su lugar.
    public static function canDelete(Model $record): bool
    {
        return static::esAdministradorActivo() && ! $record->convocatorias()->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return CategoriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategorias::route('/'),
            'create' => CreateCategoria::route('/create'),
            'edit' => EditCategoria::route('/{record}/edit'),
        ];
    }
}
