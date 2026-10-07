<?php

namespace App\Filament\Resources\Fuentes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FuenteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre')
                    ->label('Nombre de la fuente'),

                TextEntry::make('url_base')
                    ->label('Dirección web')
                    ->columnSpanFull(),

                TextEntry::make('tipo_fuente')
                    ->label('Tipo de fuente')
                    ->badge(),

                TextEntry::make('frecuencia_scraping')
                    ->label('Frecuencia de consulta')
                    ->suffix(' minutos')
                    ->placeholder('—'),

                IconEntry::make('requiere_javascript')
                    ->label('Requiere JavaScript')
                    ->boolean(),

                IconEntry::make('activa')
                    ->label('Fuente activa')
                    ->boolean(),

                TextEntry::make('ultima_ejecucion')
                    ->label('Última ejecución')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin ejecutar'),

                TextEntry::make('created_at')
                    ->label('Registrada')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),

                TextEntry::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ]);
    }
}
