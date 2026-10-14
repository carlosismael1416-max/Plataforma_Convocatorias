<?php

namespace App\Filament\Resources\DescubrimientoWebs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DescubrimientoWebInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('titulo')
                    ->placeholder('-'),
                TextEntry::make('url_original')
                    ->columnSpanFull(),
                TextEntry::make('url_normalizada')
                    ->columnSpanFull(),
                TextEntry::make('url_hash'),
                TextEntry::make('dominio')
                    ->placeholder('-'),
                TextEntry::make('metodo_descubrimiento')
                    ->placeholder('-'),
                TextEntry::make('pagina_origen')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('tipo')
                    ->placeholder('-'),
                TextEntry::make('puntaje')
                    ->numeric(),
                TextEntry::make('relevancia_tematica')
                    ->placeholder('-'),
                TextEntry::make('estado_temporal')
                    ->placeholder('-'),
                TextEntry::make('origen_anio')
                    ->placeholder('-'),
                TextEntry::make('categoria_objetivo')
                    ->placeholder('-'),
                TextEntry::make('decision_objetivo')
                    ->placeholder('-'),
                TextEntry::make('puntaje_objetivo')
                    ->numeric(),
                TextEntry::make('estado'),
                TextEntry::make('estado_fuente')
                    ->placeholder('-'),
                TextEntry::make('fuente.id')
                    ->label('Fuente')
                    ->placeholder('-'),
                TextEntry::make('convocatoria.id')
                    ->label('Convocatoria')
                    ->placeholder('-'),
                TextEntry::make('descubierto_en')
                    ->dateTime(),
                TextEntry::make('ultimo_visto_en')
                    ->dateTime(),
                TextEntry::make('procesado_en')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('error_ultimo')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
