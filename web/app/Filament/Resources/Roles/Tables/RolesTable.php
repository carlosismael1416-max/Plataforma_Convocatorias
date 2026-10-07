<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Rol')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make(
                    'descripcion'
                )
                    ->label('Descripción')
                    ->placeholder(
                        'Sin descripción'
                    )
                    ->limit(65)
                    ->wrap(),

                TextColumn::make(
                    'users_count'
                )
                    ->label('Usuarios')
                    ->numeric()
                    ->sortable(),

                TextColumn::make(
                    'permisos_count'
                )
                    ->label('Permisos')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make(
                    'updated_at'
                )
                    ->label('Actualizado')
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
                SelectFilter::make(
                    'estado'
                )
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
            ])

            ->recordUrl(
                fn (
                    Role $record
                ): string =>
                    RoleResource::getUrl(
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
                'nombre'
            );
    }
}
