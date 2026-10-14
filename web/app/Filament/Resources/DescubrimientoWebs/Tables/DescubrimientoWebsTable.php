<?php

namespace App\Filament\Resources\DescubrimientoWebs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Solo lectura: los estados e intentos de un descubrimiento los maneja
 * el extractor; editarlos a mano rompe el ciclo (regla 3 de CLAUDE.md).
 */
class DescubrimientoWebsTable
{
    public static function configure(Table $table): Table
    {
        // Columnas técnicas ocultas por defecto; se pueden mostrar con
        // el selector de columnas.
        $oculta = fn (TextColumn $columna): TextColumn => $columna
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('titulo')
                    ->searchable()
                    ->limit(80)
                    ->tooltip(fn ($record): ?string => $record->titulo),
                TextColumn::make('dominio')
                    ->searchable(),
                TextColumn::make('estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('evaluacion_score')
                    ->label('Evaluación')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('categoria_objetivo')
                    ->searchable(),
                TextColumn::make('decision_objetivo')
                    ->searchable(),
                TextColumn::make('estado_temporal')
                    ->searchable(),
                TextColumn::make('intentos_procesamiento')
                    ->label('Intentos')
                    ->numeric(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
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
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
