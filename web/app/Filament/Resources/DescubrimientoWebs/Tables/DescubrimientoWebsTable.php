<?php

namespace App\Filament\Resources\DescubrimientoWebs\Tables;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use App\Models\DescubrimientoWeb;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Solo lectura: los estados e intentos de un descubrimiento los maneja
 * el extractor; editarlos a mano rompe el ciclo (regla 3 de CLAUDE.md).
 */
class DescubrimientoWebsTable
{
    private const ESTADOS = [
        'NUEVO' => 'Nuevo', 'REVISION' => 'En evaluación', 'APROBADO' => 'Aprobado',
        'EN_PROCESO' => 'Extrayendo', 'ERROR_TEMPORAL' => 'Reintentará', 'ERROR' => 'Error',
        'FUENTE_INACCESIBLE' => 'Sitio inaccesible', 'REVISION_FINAL' => 'Validando',
        'PROCESADO' => 'Convertido', 'DESCARTADO' => 'Descartado',
    ];

    private static function metodo(?string $metodo): string
    {
        return match (true) {
            $metodo === null => '',
            str_contains($metodo, 'GOOGLE') || str_contains($metodo, 'NOTICIA') => 'vía noticias',
            str_contains($metodo, 'SITEMAP') => 'vía sitemap',
            str_contains($metodo, 'WORDPRESS') => 'vía WordPress',
            default => 'vía ' . mb_strtolower(str_replace('_', ' ', $metodo)),
        };
    }

    public static function configure(Table $table): Table
    {
        // Columnas técnicas ocultas por defecto; se pueden mostrar con
        // el selector de columnas.
        $oculta = fn (TextColumn $columna): TextColumn => $columna
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (DescubrimientoWeb $record): string => DescubrimientoWebResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('titulo')
                    ->label('Descubrimiento')
                    ->state(fn (DescubrimientoWeb $record): string => $record->tituloLegible())
                    ->description(fn (DescubrimientoWeb $record): string => trim(($record->dominio ?: 'sin dominio') . ' · ' . self::metodo($record->metodo_descubrimiento), ' ·'))
                    ->searchable(['titulo', 'url_original', 'dominio'])
                    ->weight('semibold')
                    ->limit(90)
                    ->wrap()
                    ->tooltip(fn (DescubrimientoWeb $record): ?string => $record->url_original)
                    ->grow(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::ESTADOS[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'PROCESADO', 'APROBADO' => 'success',
                        'NUEVO', 'REVISION', 'REVISION_FINAL', 'EN_PROCESO', 'ERROR_TEMPORAL' => 'warning',
                        'ERROR', 'FUENTE_INACCESIBLE' => 'danger',
                        default => 'gray',
                    })
                    ->description(fn (DescubrimientoWeb $record): ?string => match ($record->decision_objetivo) {
                        'ACEPTAR' => 'Clasificador: aceptar',
                        'REVISION' => 'Clasificador: dudoso',
                        'DESCARTAR' => 'Clasificador: descartar',
                        default => null,
                    }),

                TextColumn::make('evaluacion_score')
                    ->label('Evaluación')
                    ->formatStateUsing(fn ($state): string => "{$state} / 100")
                    ->color(fn ($state): string => (int) $state >= 80 ? 'success' : 'warning')
                    ->weight('bold')
                    ->placeholder('Sin evaluar')
                    ->description('se aprueba con 80')
                    ->sortable(),

                TextColumn::make('convocatoria_id')
                    ->label('Convocatoria')
                    ->formatStateUsing(fn ($state): string => "#{$state} · ver →")
                    ->url(fn (DescubrimientoWeb $record): ?string => $record->convocatoria_id
                        ? ConvocatoriaResource::getUrl('view', ['record' => $record->convocatoria_id])
                        : null)
                    ->color('primary')
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Última revisión')
                    ->since()
                    ->description(fn (DescubrimientoWeb $record): ?string => $record->descubierto_en ? 'Hallado ' . $record->descubierto_en->format('d/m/Y') : null)
                    ->sortable(),

                $oculta(TextColumn::make('categoria_objetivo')->label('Categoría')),
                $oculta(TextColumn::make('estado_temporal')->label('Vigencia por título')),
                $oculta(TextColumn::make('intentos_procesamiento')->label('Intentos')->numeric()),
                $oculta(TextColumn::make('url_hash')->searchable()),
                $oculta(TextColumn::make('metodo_descubrimiento')->searchable()),
                $oculta(TextColumn::make('tipo')->searchable()),
                $oculta(TextColumn::make('puntaje')->numeric()->sortable()),
                $oculta(TextColumn::make('relevancia_tematica')->searchable()),
                $oculta(TextColumn::make('origen_anio')->searchable()),
                $oculta(TextColumn::make('puntaje_objetivo')->numeric()->sortable()),
                $oculta(TextColumn::make('estado_fuente')->searchable()),
                $oculta(TextColumn::make('fuente.id')->searchable()),
                $oculta(TextColumn::make('convocatoria.id')->searchable()),
                $oculta(TextColumn::make('descubierto_en')->dateTime()->sortable()),
                $oculta(TextColumn::make('ultimo_visto_en')->dateTime()->sortable()),
                $oculta(TextColumn::make('procesado_en')->dateTime()->sortable()),
                $oculta(TextColumn::make('created_at')->dateTime()->sortable()),
            ])
            ->recordActions([]);
    }
}
