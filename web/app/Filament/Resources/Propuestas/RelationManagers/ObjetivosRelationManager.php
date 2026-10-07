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

class ObjetivosRelationManager extends RelationManager
{
    protected static string $relationship = 'objetivos';

    protected static ?string $title = 'Objetivos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->label('Tipo de objetivo')
                    ->options([
                        'GENERAL' => 'General',
                        'ESPECIFICO' => 'Específico',
                    ])
                    ->required(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

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
            ->recordTitleAttribute('descripcion')
            ->columns([
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('descripcion')
                    ->label('Objetivo')
                    ->wrap(),

                TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('orden')
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar objetivo'),
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
