<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Permiso;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->columns(2)
            ->components([

                TextInput::make('nombre')
                    ->label('Nombre del rol')
                    ->required()
                    ->maxLength(100)
                    ->unique(
                        ignoreRecord: true
                    )
                    ->live(
                        onBlur: true
                    )
                    ->disabled(
                        fn (
                            ?Role $record
                        ): bool =>
                            $record !== null
                            && RoleResource::esRolBase(
                                $record
                            )
                    )
                    ->dehydrateStateUsing(
                        fn (
                            ?string $state
                        ): string =>
                            Str::upper(
                                trim(
                                    (string)
                                    $state
                                )
                            )
                    )
                    ->helperText(
                        fn (
                            ?Role $record
                        ): ?string =>
                            $record !== null
                            && RoleResource::esRolBase(
                                $record
                            )
                                ? 'Este nombre es estructural y no puede modificarse.'
                                : 'El nombre se guardará en mayúsculas.'
                    ),

                Toggle::make('estado')
                    ->label('Rol activo')
                    ->default(true)
                    ->required()
                    ->disabled(
                        fn (
                            ?Role $record
                        ): bool =>
                            $record !== null
                            && RoleResource::esRolBase(
                                $record
                            )
                    )
                    ->helperText(
                        fn (
                            ?Role $record
                        ): ?string =>
                            $record !== null
                            && RoleResource::esRolBase(
                                $record
                            )
                                ? 'Los roles base deben permanecer activos.'
                                : null
                    ),

                Textarea::make(
                    'descripcion'
                )
                    ->label('Descripción')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | AUTOSELECCIÓN DE PERMISOS
                |--------------------------------------------------------------------------
                |
                | El botón lee el nombre del rol y marca los permisos
                | correspondientes. No guarda cambios automáticamente.
                |
                */

                Actions::make([

                    Action::make(
                        'autoseleccionarPermisos'
                    )
                        ->label(
                            'Autoseleccionar permisos del rol'
                        )
                        ->icon(
                            'heroicon-o-sparkles'
                        )
                        ->color('primary')
                        ->action(
                            function (
                                Get $get,
                                Set $set
                            ): void {
                                $rol =
                                    Str::upper(
                                        trim(
                                            (string)
                                            $get(
                                                'nombre'
                                            )
                                        )
                                    );

                                $presets = [
                                    'DOCENTE' => [
                                        'ver_convocatorias',
                                        'generar_propuestas',
                                        'editar_propuestas',
                                    ],

                                    'DIRECTIVO' => [
                                        'ver_convocatorias',
                                        'aprobar_convocatorias',
                                        'generar_reportes',
                                        'ver_estadisticas',
                                    ],

                                    'SISTEMA' => [
                                        'crear_convocatorias',
                                        'editar_convocatorias',
                                        'ejecutar_scraping',
                                    ],
                                ];

                                if (
                                    $rol ===
                                    'ADMINISTRADOR'
                                ) {
                                    $ids =
                                        Permiso::query()
                                            ->orderBy(
                                                'id'
                                            )
                                            ->pluck(
                                                'id'
                                            )
                                            ->all();

                                    $set(
                                        'permisos',
                                        $ids
                                    );

                                    Notification::make()
                                        ->title(
                                            'Permisos autoseleccionados'
                                        )
                                        ->body(
                                            'Se marcaron todos los permisos para ADMINISTRADOR. Guarda el formulario para aplicar los cambios.'
                                        )
                                        ->success()
                                        ->send();

                                    return;
                                }

                                if (
                                    ! array_key_exists(
                                        $rol,
                                        $presets
                                    )
                                ) {
                                    Notification::make()
                                        ->title(
                                            'Rol sin plantilla'
                                        )
                                        ->body(
                                            'La autoselección está disponible para DOCENTE, DIRECTIVO, ADMINISTRADOR y SISTEMA.'
                                        )
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $ids =
                                    Permiso::query()
                                        ->whereIn(
                                            'nombre',
                                            $presets[
                                                $rol
                                            ]
                                        )
                                        ->orderBy(
                                            'id'
                                        )
                                        ->pluck(
                                            'id'
                                        )
                                        ->all();

                                $set(
                                    'permisos',
                                    $ids
                                );

                                Notification::make()
                                    ->title(
                                        'Permisos autoseleccionados'
                                    )
                                    ->body(
                                        'Se cargó la plantilla de permisos de '
                                        . $rol
                                        . '. Guarda el formulario para aplicar los cambios.'
                                    )
                                    ->success()
                                    ->send();
                            }
                        ),

                ])
                    ->columnSpanFull(),

                CheckboxList::make(
                    'permisos'
                )
                    ->label(
                        'Permisos asignados'
                    )
                    ->relationship(
                        'permisos',
                        'descripcion'
                    )
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable()
                    ->columnSpanFull()
                    ->helperText(
                        'Puedes modificar manualmente la selección después de aplicar la plantilla.'
                    ),
            ]);
    }
}
