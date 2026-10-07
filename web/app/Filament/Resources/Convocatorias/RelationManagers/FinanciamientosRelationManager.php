<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinanciamientosRelationManager extends RelationManager
{
    protected static string $relationship = 'financiamientos';

    protected static ?string $title = 'Financiamiento por eje estratégico';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('grupo_clave')
            ->columns([
                TextColumn::make('grupo_clave')
                    ->label('Ejes estratégicos')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            str_replace(',', ', ', $state ?? '')
                    ),

                TextColumn::make('etapa_1_anio')
                    ->label('Año etapa 1'),

                TextColumn::make('etapa_1_monto_maximo')
                    ->label('Monto etapa 1')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$'),

                TextColumn::make('etapa_2_anio')
                    ->label('Año etapa 2'),

                TextColumn::make('etapa_2_monto_maximo')
                    ->label('Monto etapa 2')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$'),

                TextColumn::make('monto_maximo_total')
                    ->label('Total máximo')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$')
                    ->weight('bold'),

                TextColumn::make('moneda')
                    ->label('Moneda')
                    ->badge(),
            ]);
    }
}
