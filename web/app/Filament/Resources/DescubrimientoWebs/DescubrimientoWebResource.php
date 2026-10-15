<?php

namespace App\Filament\Resources\DescubrimientoWebs;

use App\Filament\Resources\DescubrimientoWebs\Pages\ListDescubrimientoWebs;
use App\Filament\Resources\DescubrimientoWebs\Pages\ViewDescubrimientoWeb;
use App\Filament\Resources\DescubrimientoWebs\Schemas\DescubrimientoWebInfolist;
use App\Filament\Resources\DescubrimientoWebs\Tables\DescubrimientoWebsTable;
use App\Models\DescubrimientoWeb;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DescubrimientoWebResource extends Resource
{
    protected static ?string $model = DescubrimientoWeb::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static ?string $navigationLabel = 'Descubrimientos';

    protected static ?string $modelLabel = 'descubrimiento';

    protected static ?string $pluralModelLabel = 'descubrimientos';

    protected static ?int $navigationSort = 6;

    /*
     * Solo lectura: los estados e intentos los maneja el extractor;
     * editarlos a mano rompe el ciclo (regla 3 de CLAUDE.md).
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return DescubrimientoWebInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DescubrimientoWebsTable::configure($table);
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
            'index' => ListDescubrimientoWebs::route('/'),
            'view' => ViewDescubrimientoWeb::route('/{record}'),
        ];
    }
}
