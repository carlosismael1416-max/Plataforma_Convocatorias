<?php

namespace App\Filament\Resources\DescubrimientoWebs\Schemas;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\DescubrimientoWeb;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DescubrimientoWebInfolist
{
    /** Color de cada estado del descubrimiento. */
    private const COLORES_ESTADO = [
        'NUEVO' => 'info',
        'REVISION' => 'warning',
        'APROBADO' => 'success',
        'EN_PROCESO' => 'info',
        'PROCESADO' => 'success',
        'REVISION_FINAL' => 'warning',
        'ERROR_TEMPORAL' => 'danger',
        'ERROR' => 'danger',
        'FUENTE_INACCESIBLE' => 'danger',
        'DESCARTADO' => 'gray',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                View::make('filament.admin.detalle.estado-descubrimiento')
                    ->columnSpanFull(),

                Section::make('Resumen')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('titulo')
                            ->label('Título')
                            ->placeholder('Sin título detectado')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str_replace('_', ' ', (string) $state))
                            ->color(fn (?string $state): string => self::COLORES_ESTADO[$state] ?? 'gray'),
                        TextEntry::make('decision_objetivo')
                            ->label('Decisión del clasificador')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'ACEPTAR' => 'success',
                                'REVISION' => 'warning',
                                'DESCARTAR' => 'danger',
                                default => 'gray',
                            })
                            ->placeholder('—'),
                        TextEntry::make('evaluacion_score')
                            ->label('Puntaje del evaluador')
                            ->suffix(' / 100')
                            ->color(fn ($state): string => (int) $state >= 80 ? 'success' : 'warning')
                            ->weight('bold')
                            ->placeholder('Sin evaluar'),
                        TextEntry::make('convocatoria_id')
                            ->label('Convocatoria creada')
                            ->formatStateUsing(fn ($state): string => "#{$state} · ver")
                            ->url(fn (DescubrimientoWeb $record): ?string => $record->convocatoria_id
                                ? ConvocatoriaResource::getUrl('view', ['record' => $record->convocatoria_id])
                                : null)
                            ->color('primary')
                            ->placeholder('Ninguna'),
                    ]),

                Section::make('Enlace')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('url_original')
                            ->label('Dirección')
                            ->url(fn (DescubrimientoWeb $record): string => $record->url_original, shouldOpenInNewTab: true)
                            ->color('primary')
                            ->copyable()
                            ->columnSpanFull(),
                        TextEntry::make('dominio')->label('Dominio')->placeholder('—'),
                        TextEntry::make('metodo_descubrimiento')
                            ->label('Cómo se encontró')
                            ->badge()
                            ->color('gray')
                            ->placeholder('—'),
                        TextEntry::make('pagina_origen')
                            ->label('Página de origen')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Fechas')
                    ->icon(Heroicon::OutlinedClock)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('descubierto_en')->label('Descubierto')->dateTime('d/m/Y H:i'),
                        TextEntry::make('ultimo_visto_en')->label('Visto por última vez')->dateTime('d/m/Y H:i'),
                        TextEntry::make('procesado_en')->label('Procesado')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('intentos_procesamiento')->label('Intentos de extracción')->placeholder('0'),
                    ]),

                Section::make('Clasificación')
                    ->icon(Heroicon::OutlinedTag)
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('categoria_objetivo')->label('Categoría')->badge()->color('info')->placeholder('—'),
                        TextEntry::make('relevancia_tematica')->label('Relevancia')->badge()->color('gray')->placeholder('—'),
                        TextEntry::make('estado_temporal')->label('Vigencia por el título')->badge()->color('gray')->placeholder('—'),
                        TextEntry::make('puntaje_objetivo')->label('Puntaje del clasificador')->placeholder('—'),
                        TextEntry::make('razones_objetivo')
                            ->label('Razones del clasificador')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('—')
                            ->columnSpan(2),
                        TextEntry::make('razones')
                            ->label('Razones del descubridor')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('—')
                            ->columnSpan(2),
                    ]),

                Section::make('Último error')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->columnSpanFull()
                    ->visible(fn (DescubrimientoWeb $record): bool => filled($record->error_ultimo))
                    ->schema([
                        TextEntry::make('error_ultimo')->hiddenLabel()->color('danger'),
                    ]),
            ]);
    }
}
