<?php

namespace App\Filament\Resources\Convocatorias\Schemas;

use App\Filament\Schemas\CamposTipoConvocatoria;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ConvocatoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                Section::make('Convocatoria')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columns(2)
                    ->columnSpan(['default' => 1, 'xl' => 2])
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título de la convocatoria')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                        ...CamposTipoConvocatoria::campos(),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('objetivo')
                            ->label('Objetivo')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Origen y estado')
                    ->icon(Heroicon::OutlinedBuildingLibrary)
                    ->columnSpan(['default' => 1, 'xl' => 1])
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'PENDIENTE_REVISION' => 'No publicada (interna)',
                                'PUBLICADA' => 'Publicada',
                                'ACTIVA' => 'Activa',
                                'CERRADA' => 'Cerrada',
                                'DESCARTADA' => 'Descartada',
                                'ARCHIVADA' => 'Archivada',
                            ])
                            ->default('PENDIENTE_REVISION')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Lo decide el motor: publica lo que pasa el validador y cierra las vencidas.'),
                        Select::make('organismo_id')
                            ->label('Organismo')
                            ->relationship('organismo', 'nombre')
                            ->searchable()
                            ->preload(),
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship('categoria', 'nombre')
                            ->searchable()
                            ->preload(),
                        Select::make('fuente_id')
                            ->label('Fuente web')
                            ->relationship('fuente', 'nombre')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional para las registradas a mano.'),
                        TextInput::make('url_original')
                            ->label('Enlace oficial')
                            ->url()
                            ->maxLength(2048)
                            ->prefixIcon(Heroicon::OutlinedGlobeAlt),
                    ]),

                Section::make('Fechas y apoyo económico')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->columns(3)
                    ->columnSpan(['default' => 1, 'xl' => 2])
                    ->schema([
                        DatePicker::make('fecha_publicacion')
                            ->label('Fecha de publicación'),
                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio'),
                        DatePicker::make('fecha_cierre')
                            ->label('Fecha de cierre')
                            ->rules(['after_or_equal:fecha_inicio']),
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
                        Select::make('moneda')
                            ->label('Moneda')
                            ->options([
                                'MXN' => 'Peso mexicano',
                                'USD' => 'Dólar estadounidense',
                                'EUR' => 'Euro',
                            ])
                            ->default('MXN')
                            ->required(),
                        TextInput::make('modalidad')
                            ->label('Modalidad')
                            ->placeholder('Nacional, estatal, internacional...')
                            ->maxLength(100),
                        TextInput::make('ubicacion')
                            ->label('Ubicación')
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
