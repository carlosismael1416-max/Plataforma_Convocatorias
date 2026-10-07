<?php

namespace App\Filament\Docente\Resources\Convocatorias;

use App\Filament\Docente\Resources\Convocatorias\Pages\CreateConvocatoria;
use App\Filament\Docente\Resources\Convocatorias\Pages\EditConvocatoria;
use App\Filament\Docente\Resources\Convocatorias\Pages\ListConvocatorias;
use App\Filament\Docente\Resources\Convocatorias\Pages\ViewConvocatoria;
use App\Filament\Docente\Resources\Convocatorias\Schemas\ConvocatoriaForm;
use App\Filament\Docente\Resources\Convocatorias\Schemas\ConvocatoriaInfolist;
use App\Filament\Docente\Resources\Convocatorias\Tables\ConvocatoriasTable;
use App\Models\Convocatoria;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConvocatoriaResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Convocatoria::class;

    protected static ?string $navigationLabel =
    'Subir convocatoria';

    protected static ?string $modelLabel =
    'Convocatoria';

    protected static ?string $pluralModelLabel =
    'Convocatorias subidas';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'titulo';

    private static function esDocenteActivo(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(
                'subido_por_user_id',
                auth()->id() ?? 0
            );
    }

    public static function canViewAny(): bool
    {
        return static::esDocenteActivo();
    }

    public static function canCreate(): bool
    {
        return static::esDocenteActivo();
    }

    public static function canView(Model $record): bool
    {
        return static::esDocenteActivo()
            && (int) $record->subido_por_user_id
                === (int) auth()->id();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canView($record)
            && in_array(
                $record->estado,
                [
                    'PENDIENTE_REVISION',
                    'REQUIERE_CORRECCIONES',
                ],
                true
            );
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ConvocatoriaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConvocatoriaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConvocatoriasTable::configure($table);
    }

        public static function getNavigationUrl(): string
{
        return static::getUrl('create');
}
    public static function getPages(): array
    {
        return [
            'index' => ListConvocatorias::route('/'),
            'create' => CreateConvocatoria::route('/create'),
            'view' => ViewConvocatoria::route('/{record}'),
            'edit' => EditConvocatoria::route('/{record}/edit'),
        ];
    }
}
