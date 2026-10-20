<?php

namespace App\Filament\Resources\Convocatorias;

use App\Filament\Resources\Convocatorias\Pages\CreateConvocatoria;
use App\Filament\Resources\Convocatorias\Pages\EditConvocatoria;
use App\Filament\Resources\Convocatorias\Pages\ListConvocatorias;
use App\Filament\Resources\Convocatorias\Pages\ViewConvocatoria;
use App\Filament\Resources\Convocatorias\Schemas\ConvocatoriaForm;
use App\Filament\Resources\Convocatorias\Schemas\ConvocatoriaInfolist;
use App\Filament\Resources\Convocatorias\Tables\ConvocatoriasTable;
use App\Models\Convocatoria;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConvocatoriaResource extends Resource
{
    protected static ?string $navigationLabel =
        'Convocatorias';

    protected static ?string $modelLabel =
        'convocatoria';

    protected static ?string $pluralModelLabel =
        'Gestión de Convocatorias';

    protected static ?int $navigationSort =
        8;

    protected static ?string $model =
        Convocatoria::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute =
        'titulo';

    public static function form(
        Schema $schema
    ): Schema {
        return ConvocatoriaForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return ConvocatoriaInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return ConvocatoriasTable::configure(
            $table
        );
    }

    public static function canAccess(): bool
    {
        return static::administradorActivo();
    }

    public static function canViewAny(): bool
    {
        return static::administradorActivo();
    }

    public static function canCreate(): bool
    {
        return static::administradorActivo();
    }

    public static function canView(
        Model $record
    ): bool {
        return static::administradorActivo();
    }

    public static function canEdit(
        Model $record
    ): bool {
        return static::administradorActivo();
    }

    public static function canDelete(
        Model $record
    ): bool {
        if (
            ! static::administradorActivo()
            || ! $record instanceof Convocatoria
        ) {
            return false;
        }

        return ! static::tieneDependencias(
            $record
        );
    }

    public static function tieneDependencias(
        Convocatoria $convocatoria
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Tablas que pueden conservar información relacionada
        |--------------------------------------------------------------------------
        |
        | Se comprueba la tabla real antes de consultarla. Esto también evita
        | errores por nombres históricos como propuesta_bases/propuestas_base
        | o notificacions/notificaciones.
        |
        */

        $tablas = [
            'propuestas',
            'propuestas_base',
            'propuesta_bases',
            'usuario_convocatorias',
            'convocatoria_archivos',
            'convocatoria_requisitos',
            'convocatoria_revisions',
            'convocatoria_revisiones_administrativas',
            'convocatoria_financiamientos',
            'convocatoria_modalidades',
            'convocatoria_apoyos',
            'notificaciones',
            'notificacions',
            'eventos_calendario',
            'api_eventos',
        ];

        // canDelete() se evalúa por cada fila de la tabla: la lista de
        // tablas existentes se calcula una vez por petición y cada fila
        // se resuelve con una sola consulta.
        static::$tablasDependientes ??= DB::table(
            'information_schema.columns'
        )
            ->whereRaw('table_schema = current_schema()')
            ->where('column_name', 'convocatoria_id')
            ->whereIn('table_name', $tablas)
            ->pluck('table_name')
            ->all();

        $gramatica = DB::getQueryGrammar();

        $condiciones = array_map(
            fn (string $tabla): string =>
                'EXISTS (SELECT 1 FROM '
                . $gramatica->wrapTable($tabla)
                . ' WHERE convocatoria_id = ?)',
            static::$tablasDependientes
        );

        // Un padre (convocatoria marco) conserva a sus hijas.
        $condiciones[] =
            'EXISTS (SELECT 1 FROM convocatorias'
            . ' WHERE convocatoria_padre_id = ?)';

        return (bool) DB::selectOne(
            'SELECT (' . implode(' OR ', $condiciones) . ') AS tiene',
            array_fill(
                0,
                count($condiciones),
                $convocatoria->id
            )
        )->tiene;
    }

    /** @var list<string>|null */
    private static ?array $tablasDependientes = null;

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Convocatorias\RelationManagers\ArchivosRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\RequisitosRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\RevisionesRelationManager::class,
        ];
    }

    /** La tabla muestra el organismo bajo cada título: se carga de una vez. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('organismo');
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListConvocatorias::route('/'),

            'create' =>
                CreateConvocatoria::route(
                    '/create'
                ),

            'view' =>
                ViewConvocatoria::route(
                    '/{record}'
                ),

            'edit' =>
                EditConvocatoria::route(
                    '/{record}/edit'
                ),
        ];
    }

    private static function administradorActivo(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }
}
