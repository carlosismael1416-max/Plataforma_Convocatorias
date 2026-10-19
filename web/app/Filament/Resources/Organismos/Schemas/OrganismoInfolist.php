<?php

namespace App\Filament\Resources\Organismos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrganismoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre')->label('Nombre'),
                TextEntry::make('descripcion')->label('Descripción')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('sitio_web')->label('Sitio web')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('tipo_organismo')->label('Tipo de organismo'),
                TextEntry::make('pais')->label('País')
                    ->placeholder('-'),
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
