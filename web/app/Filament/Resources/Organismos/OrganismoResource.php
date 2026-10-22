<?php

namespace App\Filament\Resources\Organismos;

use App\Filament\Resources\Organismos\Pages\CreateOrganismo;
use App\Filament\Resources\Organismos\Pages\EditOrganismo;
use App\Filament\Resources\Organismos\Pages\ListOrganismos;
use App\Filament\Resources\Organismos\Schemas\OrganismoForm;
use App\Filament\Resources\Organismos\Tables\OrganismosTable;
use App\Models\Organismo;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Catálogo de organismo: pestaña de Convocatorias en el admin (fuera del menú). */
class OrganismoResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Organismo::class;

    protected static ?string $modelLabel = 'organismo';

    protected static ?string $pluralModelLabel = 'organismos';

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

    // Borrar dejaría convocatorias sin organismo; se desactiva en su lugar.
    public static function canDelete(Model $record): bool
    {
        return static::esAdministradorActivo() && ! $record->convocatorias()->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return OrganismoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganismosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganismos::route('/'),
            'create' => CreateOrganismo::route('/create'),
            'edit' => EditOrganismo::route('/{record}/edit'),
        ];
    }
}
