<?php

namespace App\Filament\Resources\DescubrimientoWebs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DescubrimientoWebForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo'),
                Textarea::make('url_original')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('url_normalizada')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('url_hash')
                    ->required(),
                TextInput::make('dominio'),
                TextInput::make('metodo_descubrimiento'),
                Textarea::make('pagina_origen')
                    ->columnSpanFull(),
                TextInput::make('tipo'),
                TextInput::make('puntaje')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('relevancia_tematica'),
                TextInput::make('estado_temporal'),
                TextInput::make('anios_detectados'),
                TextInput::make('origen_anio'),
                TextInput::make('razones'),
                TextInput::make('categoria_objetivo'),
                TextInput::make('decision_objetivo'),
                TextInput::make('puntaje_objetivo')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('razones_objetivo'),
                TextInput::make('estado')
                    ->required()
                    ->default('NUEVO'),
                TextInput::make('estado_fuente'),
                TextInput::make('evidencia'),
                Select::make('fuente_id')
                    ->relationship('fuente', 'id'),
                Select::make('convocatoria_id')
                    ->relationship('convocatoria', 'id'),
                DateTimePicker::make('descubierto_en')
                    ->required(),
                DateTimePicker::make('ultimo_visto_en')
                    ->required(),
                DateTimePicker::make('procesado_en'),
                Textarea::make('error_ultimo')
                    ->columnSpanFull(),
            ]);
    }
}
