<?php

namespace App\Filament\Resources\Propuestas;

use App\Filament\Resources\Propuestas\Pages\CreatePropuesta;
use App\Filament\Resources\Propuestas\Pages\EditPropuesta;
use App\Filament\Resources\Propuestas\Pages\ListPropuestas;
use App\Filament\Resources\Propuestas\Pages\ViewPropuesta;
use App\Filament\Resources\Propuestas\Schemas\PropuestaForm;
use App\Filament\Resources\Propuestas\Schemas\PropuestaInfolist;
use App\Filament\Resources\Propuestas\Tables\PropuestasTable;
use App\Models\Propuesta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Propuestas\RelationManagers\ItemsPresupuestoRelationManager;
use App\Filament\Resources\Propuestas\RelationManagers\ObjetivosRelationManager;
use App\Filament\Resources\Propuestas\RelationManagers\ActividadesCronogramaRelationManager;
use App\Filament\Resources\Propuestas\RelationManagers\CotizacionesRelationManager;
use App\Filament\Resources\Propuestas\RelationManagers\EntregablesRelationManager;

class PropuestaResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Propuesta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return PropuestaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PropuestaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropuestasTable::configure($table);
    }

    public static function getRelations(): array
    {
          return [
        ObjetivosRelationManager::class,
        ItemsPresupuestoRelationManager::class,
        CotizacionesRelationManager::class,
        ActividadesCronogramaRelationManager::class,
        EntregablesRelationManager::class,
    ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropuestas::route('/'),
            'create' => CreatePropuesta::route('/create'),
            'view' => ViewPropuesta::route('/{record}'),
            'edit' => EditPropuesta::route('/{record}/edit'),
        ];
    }
}
