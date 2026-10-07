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

class ActividadesCronogramaRelationManager extends RelationManager
{
    protected static string $relationship = 'actividadesCronograma';

    protected static ?string $title = 'Cronograma';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('actividad')
                    ->label('Actividad')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->required(),

                DatePicker::make('fecha_fin')
                    ->label('Fecha de finalización')
                    ->required()
                    ->rules([
                        'after_or_equal:fecha_inicio',
                    ]),

                TextInput::make('responsable')
                    ->label('Responsable')
                    ->maxLength(255),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'EN_PROCESO' => 'En proceso',
                        'COMPLETADA' => 'Completada',
                        'CANCELADA' => 'Cancelada',
                    ])
                    ->default('PENDIENTE')
                    ->required(),

                TextInput::make('porcentaje_avance')
                    ->label('Avance')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(0),

                TextInput::make('orden')
                    ->label('Orden')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('actividad')
            ->columns([
                TextColumn::make('orden')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('actividad')
                    ->label('Actividad')
                    ->searchable(),

                TextColumn::make('responsable')
                    ->label('Responsable'),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y'),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y'),

                TextColumn::make('porcentaje_avance')
                    ->label('Avance')
                    ->suffix('%'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->defaultSort('orden')
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar actividad'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
