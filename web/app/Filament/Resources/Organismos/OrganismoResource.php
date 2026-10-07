<?php

namespace App\Filament\Resources\Organismos;

use App\Filament\Resources\Organismos\Pages\CreateOrganismo;
use App\Filament\Resources\Organismos\Pages\EditOrganismo;
use App\Filament\Resources\Organismos\Pages\ListOrganismos;
use App\Filament\Resources\Organismos\Pages\ViewOrganismo;
use App\Filament\Resources\Organismos\Schemas\OrganismoForm;
use App\Filament\Resources\Organismos\Schemas\OrganismoInfolist;
use App\Filament\Resources\Organismos\Tables\OrganismosTable;
use App\Models\Organismo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrganismoResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Organismo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return OrganismoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrganismoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganismosTable::configure($table);
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
            'index' => ListOrganismos::route('/'),
            'create' => CreateOrganismo::route('/create'),
            'view' => ViewOrganismo::route('/{record}'),
            'edit' => EditOrganismo::route('/{record}/edit'),
        ];
    }
}
