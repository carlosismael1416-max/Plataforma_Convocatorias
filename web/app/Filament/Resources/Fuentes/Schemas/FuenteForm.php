<?php

namespace App\Filament\Resources\Fuentes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FuenteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre de la fuente')
                    ->placeholder('Ejemplo: Portal de convocatorias')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('url_base')
                    ->label('Dirección web')
                    ->placeholder('https://ejemplo.org/convocatorias')
                    ->url()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Select::make('tipo_fuente')
                    ->label('Tipo de fuente')
                    ->options([
                        'WEB' => 'Página web',
                        'API' => 'API',
                        'RSS' => 'RSS',
                        'OTRO' => 'Otro',
                    ])
                    ->default('WEB')
                    ->required(),

                TextInput::make('frecuencia_scraping')
                    ->label('Frecuencia de consulta')
                    ->numeric()
                    ->integer()
                    ->minValue(60)
                    ->default(1440)
                    ->suffix('minutos')
                    ->helperText(
                        '1440 minutos equivalen a una consulta diaria.'
                    ),

                Toggle::make('requiere_javascript')
                    ->label('Requiere JavaScript')
                    ->helperText(
                        'Actívalo si la página carga su contenido dinámicamente.'
                    )
                    ->default(false),

                Toggle::make('activa')
                    ->label('Fuente activa')
                    ->helperText(
                        'Por ahora deja desactivada la fuente hasta configurar el extractor.'
                    )
                    ->default(false),
            ]);
    }
}
