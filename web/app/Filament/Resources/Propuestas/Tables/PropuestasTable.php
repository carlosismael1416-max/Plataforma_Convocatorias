<?php

namespace App\Filament\Resources\Propuestas\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropuestasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Propuesta')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('usuario.name')
                    ->label('Responsable')
                    ->searchable(),

                TextColumn::make('convocatoria.titulo')
                    ->label('Convocatoria')
                    ->limit(45),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),

                TextColumn::make('version')
                    ->label('Versión'),

                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'BORRADOR' => 'Borrador',
                        'EDITANDO' => 'En edición',
                        'LISTA' => 'Lista para revisión',
                        'ENVIADA' => 'Enviada',
                        'APROBADA' => 'Aprobada',
                        'RECHAZADA' => 'Rechazada',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
