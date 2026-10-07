<?php

namespace App\Filament\Resources\Propuestas\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EntregablesRelationManager extends RelationManager
{
    protected static string $relationship = 'entregables';

    protected static ?string $title = 'Entregables';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre del entregable')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('cronograma_actividad_id')
                    ->label('Actividad relacionada')
                    ->options(
                        fn (): array =>
                            $this->getOwnerRecord()
                                ->actividadesCronograma()
                                ->pluck('actividad', 'id')
                                ->toArray()
                    )
                    ->searchable()
                    ->nullable(),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'EN_PROCESO' => 'En proceso',
                        'ENTREGADO' => 'Entregado',
                        'APROBADO' => 'Aprobado',
                        'RECHAZADO' => 'Rechazado',
                    ])
                    ->default('PENDIENTE')
                    ->required(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                DatePicker::make('fecha_limite')
                    ->label('Fecha límite'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Entregable')
                    ->searchable(),

                TextColumn::make('actividad.actividad')
                    ->label('Actividad')
                    ->placeholder('Sin actividad'),

                TextColumn::make('fecha_limite')
                    ->label('Fecha límite')
                    ->date('d/m/Y'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),

                TextColumn::make('fecha_entrega')
                    ->label('Fecha de entrega')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin entregar'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar entregable'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
