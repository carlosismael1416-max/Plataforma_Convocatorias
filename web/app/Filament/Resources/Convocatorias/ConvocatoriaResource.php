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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class ConvocatoriaResource extends Resource
{
    protected static ?string $navigationLabel =
        'Gestión de Convocatorias';

    protected static ?string $modelLabel =
        'convocatoria';

    protected static ?string $pluralModelLabel =
        'Gestión de Convocatorias';

    protected static ?int $navigationSort =
        7;

    protected static ?string $model =
        Convocatoria::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

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
            'evento_calendarios',
            'api_eventos',
        ];

        foreach ($tablas as $tabla) {
            if (
                ! DatabaseSchema::hasTable($tabla)
                || ! DatabaseSchema::hasColumn(
                    $tabla,
                    'convocatoria_id'
                )
            ) {
                continue;
            }

            if (
                DB::table($tabla)
                    ->where(
                        'convocatoria_id',
                        $convocatoria->id
                    )
                    ->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Convocatorias\RelationManagers\ModalidadesFinanciamientoRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\ApoyosFinancierosRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\FinanciamientosRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\ArchivosRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\RequisitosRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\RevisionesRelationManager::class,
            \App\Filament\Resources\Convocatorias\RelationManagers\RevisionesAdministrativasRelationManager::class,
        ];
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
