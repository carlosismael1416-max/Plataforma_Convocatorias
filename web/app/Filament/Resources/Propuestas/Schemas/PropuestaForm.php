<?php

namespace App\Filament\Resources\Propuestas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PropuestaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('titulo')
                    ->label('Título de la propuesta')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Select::make('user_id')
                    ->label('Responsable')
                    ->relationship('usuario', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('convocatoria_id')
                    ->label('Convocatoria')
                    ->relationship('convocatoria', 'titulo')
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('resumen')
                    ->label('Resumen')
                    ->rows(4)
                    ->columnSpanFull(),

                Textarea::make('justificacion')
                    ->label('Justificación')
                    ->rows(5)
                    ->columnSpanFull(),

                Textarea::make('metodologia')
                    ->label('Metodología')
                    ->rows(5)
                    ->columnSpanFull(),

                Textarea::make('impacto_esperado')
                    ->label('Impacto esperado')
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'BORRADOR' => 'Borrador',
                        'EDITANDO' => 'En edición',
                        'LISTA' => 'Lista para revisión',
                    ])
                    ->default('BORRADOR')
                    ->required(),
            ]);
    }
}
