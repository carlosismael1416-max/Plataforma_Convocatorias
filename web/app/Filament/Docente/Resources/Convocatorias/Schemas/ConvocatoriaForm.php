<?php

namespace App\Filament\Docente\Resources\Convocatorias\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ConvocatoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                Group::make([
                    Section::make('Información básica')
                        ->icon(Heroicon::OutlinedBuildingOffice2)
                        ->columns(2)
                        ->schema([
                            TextInput::make('titulo')
                                ->label('Título de la convocatoria')
                                ->placeholder('Ej.: PRODEP 2026 – Fortalecimiento a Cuerpos Académicos')
                                ->required()
                                ->maxLength(500)
                                ->columnSpanFull(),

                            Select::make('organismo_id')
                                ->label('Organismo financiador')
                                ->relationship('organismo', 'nombre')
                                ->searchable()
                                ->preload(),

                            Select::make('categoria_id')
                                ->label('Categoría')
                                ->relationship('categoria', 'nombre')
                                ->searchable()
                                ->preload(),

                            TextInput::make('modalidad')
                                ->label('Modalidad')
                                ->placeholder('Presencial, en línea o mixta')
                                ->maxLength(100),

                            TextInput::make('ubicacion')
                                ->label('Ubicación')
                                ->maxLength(255),

                            TextInput::make('url_original')
                                ->label('Enlace de la convocatoria original')
                                ->placeholder('https://organismo.gob.mx/convocatorias/...')
                                ->prefixIcon(Heroicon::OutlinedGlobeAlt)
                                ->url()
                                ->maxLength(2048)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Descripción y objetivo')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([
                            Textarea::make('descripcion')
                                ->label('Descripción general')
                                ->placeholder('Propósito, alcance y a quién va dirigida la convocatoria...')
                                ->required()
                                ->rows(5),

                            Textarea::make('objetivo')
                                ->label('Objetivo de la convocatoria')
                                ->placeholder('¿Qué busca financiar o apoyar?')
                                ->rows(3),
                        ]),

                    Section::make('Fechas y apoyo económico')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->columns(3)
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
                                    'MXN' => 'MXN – Peso mexicano',
                                    'USD' => 'USD – Dólar',
                                    'EUR' => 'EUR – Euro',
                                ])
                                ->default('MXN')
                                ->required(),
                        ]),
                ])->columnSpan(['default' => 1, 'xl' => 2]),

                Group::make([
                    Section::make('Consejos')
                        ->icon(Heroicon::OutlinedLightBulb)
                        ->schema([
                            Text::make('• Usa un título claro, con el programa y el año.'),
                            Text::make('• Agrega el enlace oficial para que se pueda verificar la fuente.'),
                            Text::make('• La fecha de cierre alimenta el calendario y los avisos de vencimiento.'),
                            Text::make('• Sin fecha de cierre, el directivo decide si se publica.'),
                        ]),

                    Section::make('¿Qué pasa al enviarla?')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->schema([
                            Text::make('Queda como pendiente de revisión. Un directivo la revisa y decide si se publica para todos los docentes.'),
                        ]),
                ])->columnSpan(1),
            ]);
    }
}
