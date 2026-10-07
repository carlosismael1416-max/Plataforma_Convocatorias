<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RequisitosRelationManager extends RelationManager
{
    protected static string $relationship = 'requisitos';

    protected static ?string $title = 'Requisitos y disposiciones';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->columns([
                TextColumn::make('orden')
                    ->label('N.º')
                    ->sortable(),

                TextColumn::make('titulo')
                    ->label('Requisito o documento')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->wrap(),

                TextColumn::make('tipo_requisito')
                    ->label('Clasificación')
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'requisito' => 'Requisito',
                            'restriccion' => 'Restricción',
                            'causa_no_elegibilidad' => 'No elegibilidad',
                            'criterio_prioridad' => 'Criterio de prioridad',
                            'condicion_financiamiento' => 'Financiamiento',
                            'documento' => 'Documento',
                            default => $state ?? 'Sin clasificar',
                        }
                    )
                    ->badge(),

                TextColumn::make('obligatorio')
                    ->label('Obligatorio')
                    ->formatStateUsing(
                        fn (bool $state): string =>
                            $state ? 'Sí' : 'No / Condicional'
                    )
                    ->badge(),
            ])
            ->defaultSort('orden', 'asc');
    }
}
