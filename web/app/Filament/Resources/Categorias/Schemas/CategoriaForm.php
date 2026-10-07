<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre de la categoría')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(150),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4),

                Toggle::make('estado')
                    ->label('Categoría activa')
                    ->default(true),
            ]);
    }
}
