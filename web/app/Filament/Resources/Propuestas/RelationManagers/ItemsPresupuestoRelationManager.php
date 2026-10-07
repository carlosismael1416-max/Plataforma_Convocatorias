<?php

namespace App\Filament\Resources\Propuestas\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsPresupuestoRelationManager extends RelationManager
{
    protected static string $relationship = 'itemsPresupuesto';

    protected static ?string $title = 'Presupuesto';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('concepto')
                    ->label('Concepto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->minValue(0.01)
                    ->default(1)
                    ->required(),

                TextInput::make('precio_unitario')
                    ->label('Precio unitario')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$')
                    ->required(),

                Select::make('categoria_gasto')
                    ->label('Categoría de gasto')
                    ->options([
                        'EQUIPO' => 'Equipo',
                        'MATERIALES' => 'Materiales',
                        'SERVICIOS' => 'Servicios',
                        'VIATICOS' => 'Viáticos',
                        'PERSONAL' => 'Personal',
                        'INFRAESTRUCTURA' => 'Infraestructura',
                        'OTRO' => 'Otro',
                    ]),

                Select::make('moneda')
                    ->label('Moneda')
                    ->options([
                        'MXN' => 'MXN',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default('MXN')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('concepto')
            ->columns([
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable(),

                TextColumn::make('categoria_gasto')
                    ->label('Categoría')
                    ->badge(),

                TextColumn::make('cantidad')
                    ->label('Cantidad'),

                TextColumn::make('precio_unitario')
                    ->label('Precio unitario')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('moneda')
                    ->label('Moneda'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->state(
                        fn ($record) =>
                            (float) $record->cantidad *
                            (float) $record->precio_unitario
                    )
                    ->numeric(decimalPlaces: 2),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar concepto'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
