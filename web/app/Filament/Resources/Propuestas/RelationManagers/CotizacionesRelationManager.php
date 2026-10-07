<?php

namespace App\Filament\Resources\Propuestas\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CotizacionesRelationManager extends RelationManager
{
    protected static string $relationship = 'cotizaciones';

    protected static ?string $title = 'Cotizaciones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('presupuesto_item_id')
                    ->label('Concepto del presupuesto')
                    ->options(
                        fn (): array =>
                            $this->getOwnerRecord()
                                ->itemsPresupuesto()
                                ->pluck('concepto', 'id')
                                ->toArray()
                    )
                    ->searchable()
                    ->nullable(),

                TextInput::make('proveedor')
                    ->label('Proveedor')
                    ->maxLength(255),

                TextInput::make('concepto')
                    ->label('Concepto')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('monto')
                    ->label('Monto')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                Select::make('moneda')
                    ->label('Moneda')
                    ->options([
                        'MXN' => 'MXN',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default('MXN')
                    ->required(),

                DatePicker::make('fecha_cotizacion')
                    ->label('Fecha de cotización'),

                TextInput::make('archivo_url')
                    ->label('URL del archivo')
                    ->url()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('proveedor')
            ->columns([
                TextColumn::make('proveedor')
                    ->label('Proveedor')
                    ->searchable(),

                TextColumn::make('presupuestoItem.concepto')
                    ->label('Presupuesto')
                    ->placeholder('Sin concepto'),

                TextColumn::make('monto')
                    ->label('Monto')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('moneda')
                    ->label('Moneda')
                    ->badge(),

                TextColumn::make('fecha_cotizacion')
                    ->label('Fecha')
                    ->date('d/m/Y'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar cotización'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
