<?php

namespace App\Filament\Resources\DescubrimientoWebs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DescubrimientoWebsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('url_hash')
                    ->searchable(),
                TextColumn::make('dominio')
                    ->searchable(),
                TextColumn::make('metodo_descubrimiento')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->searchable(),
                TextColumn::make('puntaje')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('relevancia_tematica')
                    ->searchable(),
                TextColumn::make('estado_temporal')
                    ->searchable(),
                TextColumn::make('origen_anio')
                    ->searchable(),
                TextColumn::make('categoria_objetivo')
                    ->searchable(),
                TextColumn::make('decision_objetivo')
                    ->searchable(),
                TextColumn::make('puntaje_objetivo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado')
                    ->searchable(),
                TextColumn::make('estado_fuente')
                    ->searchable(),
                TextColumn::make('fuente.id')
                    ->searchable(),
                TextColumn::make('convocatoria.id')
                    ->searchable(),
                TextColumn::make('descubierto_en')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ultimo_visto_en')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('procesado_en')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
