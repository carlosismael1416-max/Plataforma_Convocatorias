<?php

namespace App\Filament\Resources\Fuentes\Schemas;

use App\Filament\Admin\Pages\GestionarFuente;
use App\Models\Fuente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FuenteForm
{
    /** Frecuencias habituales (minutos => texto); el ciclo corre cada 6 h. */
    private const FRECUENCIAS = [
        360 => 'Cada 6 horas (en cada ciclo)',
        720 => 'Cada 12 horas',
        1440 => 'Una vez al día (recomendado)',
        4320 => 'Cada 3 días',
        10080 => 'Una vez a la semana',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'xl' => 2])
            ->components([
                Section::make('Datos de la fuente')
                    ->description('Qué sitio es y dónde publica sus convocatorias.')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre de la fuente')
                            ->placeholder('Ejemplo: SECIHTI - Convocatorias')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('url_base')
                            ->label('Página de convocatorias')
                            ->placeholder('https://ejemplo.gob.mx/convocatorias')
                            ->helperText('La página donde el sitio lista sus convocatorias (no la portada, si tiene una sección propia).')
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
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Consulta automática')
                    ->description('Cómo y cada cuánto la revisa el motor de extracción.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Toggle::make('activa')
                            ->label('Fuente activa')
                            ->helperText('Solo las fuentes activas se consultan en el ciclo automático.')
                            ->default(true),

                        Select::make('frecuencia_scraping')
                            ->label('¿Cada cuánto se consulta?')
                            ->options(fn (?Fuente $record): array => self::opcionesFrecuencia($record?->frecuencia_scraping))
                            ->default(1440)
                            ->native(false)
                            ->required()
                            ->helperText('El ciclo automático corre 4 veces al día (00:00, 06:00, 12:00 y 18:00).'),

                        Toggle::make('requiere_javascript')
                            ->label('La página necesita JavaScript')
                            ->helperText('Actívalo si la página no muestra sus convocatorias sin JavaScript. Por bajo consumo, el ciclo automático no consulta estas fuentes.')
                            ->default(false),
                    ]),
            ]);
    }

    /** Incluye la frecuencia actual aunque no sea una de las habituales. */
    private static function opcionesFrecuencia(?int $actual): array
    {
        $opciones = self::FRECUENCIAS;

        if ($actual && ! isset($opciones[$actual])) {
            $opciones[$actual] = GestionarFuente::frecuenciaLegible($actual);
            ksort($opciones);
        }

        return $opciones;
    }
}
