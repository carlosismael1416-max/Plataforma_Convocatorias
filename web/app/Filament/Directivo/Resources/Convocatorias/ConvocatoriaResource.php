<?php

namespace App\Filament\Directivo\Resources\Convocatorias;

use App\Filament\Directivo\Resources\Convocatorias\Pages\CreateConvocatoria;
use App\Filament\Directivo\Resources\Convocatorias\Pages\EditConvocatoria;
use App\Filament\Directivo\Resources\Convocatorias\Pages\ListConvocatorias;
use App\Filament\Directivo\Resources\Convocatorias\Pages\ViewConvocatoria;
use App\Filament\Directivo\Resources\Convocatorias\Schemas\ConvocatoriaForm;
use App\Filament\Directivo\Resources\Convocatorias\Schemas\ConvocatoriaInfolist;
use App\Filament\Directivo\Resources\Convocatorias\Tables\ConvocatoriasTable;
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
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Mis convocatorias';

    protected static ?int $navigationSort = 3;


    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Convocatoria::class;

    protected static ?string $modelLabel = 'Convocatoria';

    protected static ?string $pluralModelLabel = 'Mis convocatorias';

    protected static ?string $recordTitleAttribute = 'titulo';

    private static function esDirectivoActivo(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DIRECTIVO');
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
        return static::esDirectivoActivo();
    }

    public static function canCreate(): bool
    {
        return static::esDirectivoActivo();
    }

    public static function canView(Model $record): bool
    {
        return static::esDirectivoActivo()
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
