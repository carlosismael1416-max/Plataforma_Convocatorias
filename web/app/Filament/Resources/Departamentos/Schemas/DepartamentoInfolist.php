<?php

namespace App\Filament\Resources\Departamentos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DepartamentoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre')->label('Nombre'),
                TextEntry::make('descripcion')->label('Descripción')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('estado')->label('Estado')
                    ->boolean(),
                TextEntry::make('created_at')->label('Registrado')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')->label('Última actualización')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
