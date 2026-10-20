<?php

namespace App\Filament\Docente\Resources\Convocatorias\Schemas;

use App\Filament\Schemas\CamposTipoConvocatoria;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ConvocatoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        // «Subir convocatoria» tiene dos secciones (automática / manual); en
        // automática el formulario aparece tras analizar el enlace. Editar
        // no tiene modos: siempre se ve.
        $mostrar = fn ($livewire): bool => ! method_exists($livewire, 'mostrarFormulario') || $livewire->mostrarFormulario();

        return $schema
            ->columns(['default' => 1, 'xl' => 3])
            ->components([
                Group::make([
                    View::make('filament.itsva.importar-enlace')
                        ->visibleOn('create'),

                    Section::make('Información básica')
                        ->visible($mostrar)
                        ->icon(Heroicon::OutlinedBuildingOffice2)
                        ->columns(2)
                        ->schema([
                            TextInput::make('titulo')
                                ->label('Título de la convocatoria')
                                ->placeholder('Ej.: PRODEP 2026 – Fortalecimiento a Cuerpos Académicos')
                                ->required()
                                ->maxLength(500)
                                ->columnSpanFull(),

                            ...CamposTipoConvocatoria::campos(),

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
                        ->visible($mostrar)
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
                        ->visible($mostrar)
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
                            Text::make('• Si aún no conoces la fecha de cierre, agrégala después editando la convocatoria.'),
                        ]),

                    Section::make('¿Qué pasa al publicarla?')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->schema([
                            Text::make('Se publica al momento para todos los docentes y directivos. Puedes corregirla después desde «Subir convocatoria».'),
                            Text::make('Si la subes desde un enlace y no cumple los criterios automáticos, te avisamos y tú decides si publicarla de todas maneras.'),
                        ]),
                ])->columnSpan(1),
            ]);
    }
}
