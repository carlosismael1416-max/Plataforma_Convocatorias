<?php

namespace App\Filament\Resources\Convocatorias\Tables;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConvocatoriasTable
{
    /** Publicada cuya fecha de cierre ya pasó (el ciclo la cerrará). */
    private static function vencida(Convocatoria $record): bool
    {
        return in_array($record->estado, ['PUBLICADA', 'ACTIVA'], true)
            && $record->fecha_cierre !== null
            && $record->fecha_cierre->lt(today());
    }

    public static function configure(
        Table $table
    ): Table {
        return $table
            ->columns([
                // Título con el organismo debajo: una sola columna ancha en vez
                // de dos angostas (antes la tabla obligaba a desplazarse).
                TextColumn::make('titulo')
                    ->label('Convocatoria')
                    ->description(fn (Convocatoria $record): string => $record->organismo?->nombre ?? 'Sin organismo')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(
                        fn (Builder $q) => $q
                            ->where('titulo', 'ilike', "%{$search}%")
                            ->orWhereHas('organismo', fn (Builder $o) => $o->where('nombre', 'ilike', "%{$search}%"))
                    ))
                    ->sortable()
                    ->weight('semibold')
                    ->limit(95)
                    ->tooltip(fn (Convocatoria $record): ?string => mb_strlen($record->titulo) > 95 ? $record->titulo : null)
                    ->wrap()
                    ->grow(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'NO_APLICA'
                        ? 'No aplica'
                        : implode(' ', array_reverse(array_slice(Convocatoria::TIPOS[$state ?? 'FINANCIAMIENTO'] ?? [(string) $state, ''], 0, 2))))
                    ->color(fn (?string $state): array|string => match ($state) {
                        'BECA' => Color::Purple,
                        'PREMIO' => Color::Amber,
                        'CONCURSO' => Color::Pink,
                        'EVENTO' => Color::Cyan,
                        'FORMACION' => Color::Emerald,
                        'NO_APLICA' => 'gray',
                        default => Color::Blue,
                    })
                    ->default('FINANCIAMIENTO')
                    ->description(fn (Convocatoria $record): ?string => ($a = $record->etiquetasDirigidoA()) ? implode(' · ', $a) : null)
                    ->toggleable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Convocatoria $record): string => self::vencida($record)
                        ? 'Vencida'
                        : match ($state) {
                            'PENDIENTE_REVISION' => 'No publicada',
                            'PUBLICADA' => 'Publicada',
                            'ACTIVA' => 'Activa',
                            'CERRADA' => 'Cerrada',
                            'DESCARTADA' => 'Descartada',
                            'ARCHIVADA' => 'Archivada',
                            'BORRADOR' => 'Borrador',
                            'REQUIERE_CORRECCIONES' => 'Requiere correcciones',
                            default => $state,
                        })
                    ->color(fn (string $state, Convocatoria $record): string => self::vencida($record)
                        ? 'gray'
                        : match ($state) {
                            'PUBLICADA', 'ACTIVA' => 'success',
                            'PENDIENTE_REVISION' => 'warning',
                            'REQUIERE_CORRECCIONES', 'DESCARTADA' => 'danger',
                            default => 'gray',
                        })
                    ->description(fn (Convocatoria $record): string => match ($record->origen) { 'MANUAL' => '✍ Subida a mano', 'PROGRAMA' => '📅 Programa anual', default => '🤖 Motor' }),

                TextColumn::make('fecha_cierre')
                    ->label('Cierre')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin fecha')
                    ->description(fn (Convocatoria $record): ?string => $record->fecha_cierre
                        ? ($record->fecha_cierre->isToday()
                            ? 'Cierra hoy'
                            : $record->fecha_cierre->locale('es')->diffForHumans(today(), ['syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]))
                        : null)
                    ->color(fn (Convocatoria $record): ?string => $record->fecha_cierre && ! $record->fecha_cierre->isPast() && $record->fecha_cierre->diffInDays(today(), true) <= 7 ? 'warning' : null),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('Sin categoría')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fuente.nombre')
                    ->label('Fuente')
                    ->placeholder('Registro manual')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fecha_extraccion')
                    ->label('Detectada')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(collect(\App\Models\Convocatoria::TIPOS)->map(fn (array $t): string => $t[0])->all() + ['NO_APLICA' => 'No aplica (oculta)'])
                    ->query(fn ($query, array $data) => $data['value'] === 'NO_APLICA'
                        ? $query->where('tipo', 'NO_APLICA')
                        : $query->deTipo($data['value'] ?? null)),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'BORRADOR' =>
                            'Borrador',

                        'PENDIENTE_REVISION' =>
                            'Pendiente de revisión',

                        'REQUIERE_CORRECCIONES' =>
                            'Requiere correcciones',

                        'PUBLICADA' =>
                            'Publicada',

                        'ACTIVA' =>
                            'Activa',

                        'CERRADA' =>
                            'Cerrada',

                        'DESCARTADA' =>
                            'Descartada',

                        'ARCHIVADA' =>
                            'Archivada',
                    ]),

                SelectFilter::make(
                    'categoria'
                )
                    ->label('Categoría')
                    ->relationship(
                        'categoria',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'organismo'
                )
                    ->label('Organismo')
                    ->relationship(
                        'organismo',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'fuente'
                )
                    ->label('Fuente')
                    ->relationship(
                        'fuente',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'origen'
                )
                    ->label('Origen')
                    ->options([
                        'SCRAPING' =>
                            'Bot / Scraping',

                        'MANUAL' =>
                            'Carga manual',
                    ]),
            ])

            ->defaultSort(
                'created_at',
                'desc'
            )

            // Las no publicadas y descartadas abren la revisión manual.
            ->recordUrl(
                fn (Convocatoria $record): string => in_array($record->estado, \App\Services\RevisionConvocatoria::REVISABLES, true)
                    ? \App\Filament\Admin\Pages\RevisarConvocatoria::getUrl(['record' => $record->id])
                    : ConvocatoriaResource::getUrl('view', ['record' => $record])
            )

            ->recordActions([
                \Filament\Actions\Action::make('revisar')
                    ->label('Revisar y decidir')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconButton()
                    ->tooltip('Revisar y decidir')
                    ->color('warning')
                    ->visible(fn (Convocatoria $record): bool => in_array($record->estado, \App\Services\RevisionConvocatoria::REVISABLES, true))
                    ->url(fn (Convocatoria $record): string => \App\Filament\Admin\Pages\RevisarConvocatoria::getUrl(['record' => $record->id])),

                EditAction::make()
                    ->label('Editar')
                    ->iconButton()
                    ->tooltip('Editar'),

                DeleteAction::make()
                    ->label('Eliminar')
                    ->iconButton()
                    ->tooltip('Eliminar')
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Eliminar convocatoria'
                    )
                    ->modalDescription(
                        'Esta acción solamente está disponible para convocatorias sin información relacionada.'
                    )
                    ->successNotificationTitle(
                        'Convocatoria eliminada'
                    )
                    ->visible(
                        fn (
                            Convocatoria $record
                        ): bool =>
                            ConvocatoriaResource::canDelete(
                                $record
                            )
                    ),
            ]);
    }
}
