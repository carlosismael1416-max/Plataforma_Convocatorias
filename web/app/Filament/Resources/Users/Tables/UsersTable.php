<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('apellidos')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label(
                        'Correo institucional'
                    )
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make(
                    'role.nombre'
                )
                    ->label('Rol')
                    ->badge()
                    ->sortable()
                    ->color(
                        fn (
                            ?string $state
                        ): string =>
                            match ($state) {
                                'ADMINISTRADOR' =>
                                    'success',

                                'DIRECTIVO' =>
                                    'info',

                                'DOCENTE' =>
                                    'warning',

                                'SISTEMA' =>
                                    'gray',

                                default =>
                                    'gray',
                            }
                    ),

                TextColumn::make(
                    'departamento.nombre'
                )
                    ->label('Departamento')
                    ->searchable()
                    ->sortable()
                    ->placeholder(
                        'Sin departamento'
                    ),

                IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make(
                    'ultimo_acceso'
                )
                    ->label('Último acceso')
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->placeholder(
                        'Sin acceso'
                    )
                    ->sortable(),

                TextColumn::make(
                    'created_at'
                )
                    ->label('Registrado')
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),
            ])

            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->relationship(
                        'role',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'departamento'
                )
                    ->label('Departamento')
                    ->relationship(
                        'departamento',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
            ])

            /*
            |--------------------------------------------------------------------------
            | FILA COMPLETA CLICABLE
            |--------------------------------------------------------------------------
            |
            | Al hacer clic sobre cualquier usuario se abre
            | directamente su página de detalle.
            |
            */
            ->recordUrl(
                fn (User $record): string =>
                    UserResource::getUrl(
                        'view',
                        [
                            'record' =>
                                $record,
                        ]
                    )
            )

            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),

                EditAction::make()
                    ->label('Editar'),
            ])

            ->defaultSort(
                'created_at',
                'desc'
            );
    }
}
