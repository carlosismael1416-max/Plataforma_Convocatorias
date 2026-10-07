<?php

namespace App\Filament\Resources\Propuestas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PropuestaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('convocatoria.id')
                    ->label('Convocatoria'),
                TextEntry::make('propuestaBase.id')
                    ->label('Propuesta base')
                    ->placeholder('-'),
                TextEntry::make('titulo'),
                TextEntry::make('resumen')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('justificacion')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('metodologia')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('impacto_esperado')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('estado'),
                TextEntry::make('version')
                    ->numeric(),
                TextEntry::make('fecha_envio')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
