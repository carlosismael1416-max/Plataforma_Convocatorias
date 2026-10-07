<?php

namespace App\Filament\Docente\Resources\Convocatorias\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConvocatoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('titulo')
                    ->label('Título de la convocatoria')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),

                Textarea::make('objetivo')
                    ->label('Objetivo')
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload(),

                Select::make('organismo_id')
                    ->label('Organismo')
                    ->relationship('organismo', 'nombre')
                    ->searchable()
                    ->preload(),

                DatePicker::make('fecha_publicacion')
                    ->label('Fecha de publicación'),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio'),

                DatePicker::make('fecha_cierre')
                    ->label('Fecha de cierre')
                    ->rules(['after_or_equal:fecha_inicio']),

                Select::make('moneda')
                    ->label('Moneda')
                    ->options([
                        'MXN' => 'Peso mexicano',
                        'USD' => 'Dólar estadounidense',
                        'EUR' => 'Euro',
                    ])
                    ->default('MXN')
                    ->required(),

                TextInput::make('monto_minimo')
                    ->label('Monto mínimo')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),

                TextInput::make('monto_maximo')
                    ->label('Monto máximo')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),

                TextInput::make('modalidad')
                    ->label('Modalidad')
                    ->maxLength(100),

                TextInput::make('ubicacion')
                    ->label('Ubicación')
                    ->maxLength(255),

                TextInput::make('url_original')
                    ->label('Enlace de la convocatoria')
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull(),
            ]);
    }
}
