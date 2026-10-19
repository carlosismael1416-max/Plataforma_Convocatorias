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
                TextEntry::make('user_id')->label('Autor')
                    ->numeric(),
                TextEntry::make('convocatoria.id')
                    ->label('Convocatoria'),
                TextEntry::make('propuestaBase.id')
                    ->label('Propuesta base')
                    ->placeholder('-'),
                TextEntry::make('titulo')->label('Título'),
                TextEntry::make('resumen')->label('Resumen')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('justificacion')->label('Justificación')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('metodologia')->label('Metodología')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('impacto_esperado')->label('Impacto esperado')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('estado')->label('Estado'),
                TextEntry::make('version')->label('Versión')
                    ->numeric(),
                TextEntry::make('fecha_envio')->label('Fecha de envío')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')->label('Registrado')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')->label('Última actualización')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
