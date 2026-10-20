<?php

namespace App\Filament\Resources\Departamentos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')->label('Nombre del departamento')->unique(ignoreRecord: true)
                    ->required(),
                Textarea::make('descripcion')->label('Descripción')
                    ->columnSpanFull(),
                Toggle::make('estado')->label('Activo')->default(true)
                    ->required(),
            ]);
    }
}
