<?php

namespace App\Filament\Resources\DescubrimientoWebs;

use App\Filament\Resources\DescubrimientoWebs\Pages\CreateDescubrimientoWeb;
use App\Filament\Resources\DescubrimientoWebs\Pages\EditDescubrimientoWeb;
use App\Filament\Resources\DescubrimientoWebs\Pages\ListDescubrimientoWebs;
use App\Filament\Resources\DescubrimientoWebs\Pages\ViewDescubrimientoWeb;
use App\Filament\Resources\DescubrimientoWebs\Schemas\DescubrimientoWebForm;
use App\Filament\Resources\DescubrimientoWebs\Schemas\DescubrimientoWebInfolist;
use App\Filament\Resources\DescubrimientoWebs\Tables\DescubrimientoWebsTable;
use App\Models\DescubrimientoWeb;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DescubrimientoWebResource extends Resource
{
    protected static ?string $model = DescubrimientoWeb::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DescubrimientoWebForm::configure($schema);
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
            'create' => CreateDescubrimientoWeb::route('/create'),
            'view' => ViewDescubrimientoWeb::route('/{record}'),
            'edit' => EditDescubrimientoWeb::route('/{record}/edit'),
        ];
    }
}
