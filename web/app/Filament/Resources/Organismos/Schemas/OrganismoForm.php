<?php

namespace App\Filament\Resources\Organismos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrganismoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre del organismo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4),

                TextInput::make('sitio_web')
                    ->label('Sitio web')
                    ->url()
                    ->maxLength(2048),

                Select::make('tipo_organismo')
                    ->label('Tipo de organismo')
                    ->options([
                        'FEDERAL' => 'Federal',
                        'ESTATAL' => 'Estatal',
                        'MUNICIPAL' => 'Municipal',
                        'UNIVERSIDAD' => 'Universidad',
                        'EMPRESA' => 'Empresa',
                        'INTERNACIONAL' => 'Internacional',
                        'OTRO' => 'Otro',
                    ])
                    ->default('OTRO')
                    ->required(),

                TextInput::make('pais')
                    ->label('País')
                    ->maxLength(100)
                    ->default('México'),

                Toggle::make('estado')
                    ->label('Organismo activo')
                    ->default(true),
            ]);
    }
}
