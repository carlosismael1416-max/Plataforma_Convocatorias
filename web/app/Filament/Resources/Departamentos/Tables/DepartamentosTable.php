<?php

namespace App\Filament\Resources\Departamentos\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DepartamentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Departamento')
                    ->description(fn ($record): ?string => $record->descripcion ? \Illuminate\Support\Str::limit($record->descripcion, 90) : null)
                    ->weight('semibold')
                    ->searchable()
                    ->sortable()
                    ->grow(),
                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton()->tooltip('Editar'),
                \Filament\Actions\DeleteAction::make()->iconButton()->tooltip('Eliminar (solo sin usuarios)'),
            ])
            ->emptyStateHeading('Aún no hay departamentos')
            ->emptyStateDescription('Créalos aquí o directamente al registrar un usuario.');
    }
}
