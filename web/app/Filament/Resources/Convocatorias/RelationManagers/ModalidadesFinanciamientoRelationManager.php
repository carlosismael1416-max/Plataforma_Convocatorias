<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use App\Models\ConvocatoriaModalidad;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModalidadesFinanciamientoRelationManager extends RelationManager
{
    protected static string $relationship = 'modalidadesFinanciamiento';

    protected static ?string $title = 'Modalidades de financiamiento';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Modalidad')
                    ->weight('bold'),

                TextColumn::make('monto_maximo_total')
                    ->label('Monto máximo total')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('$'),

                TextColumn::make('moneda')
                    ->label('Moneda')
                    ->badge(),

                TextColumn::make('etapas_resumen')
                    ->label('Etapas')
                    ->state(
                        fn (ConvocatoriaModalidad $record): array =>
                            $record->etapas
                                ->map(
                                    fn ($etapa): string => sprintf(
                                        'Etapa %d (%d): $%s %s',
                                        $etapa->numero,
                                        $etapa->anio,
                                        number_format(
                                            (float) $etapa->monto_maximo,
                                            2
                                        ),
                                        $record->moneda
                                    )
                                )
                                ->all()
                    )
                    ->listWithLineBreaks(),

                TextColumn::make('estado_documental')
                    ->label('Estado de revisión')
                    ->badge(),

                TextColumn::make('pdf_principal')
                    ->label('PDF principal')
                    ->state(
                        fn (ConvocatoriaModalidad $record): ?string =>
                            self::nombreFuente($record, 0)
                    )
                    ->url(
                        fn (ConvocatoriaModalidad $record): ?string =>
                            self::fuente($record, 0)['url_pdf'] ?? null
                    )
                    ->openUrlInNewTab()
                    ->placeholder('Sin documento')
                    ->wrap(),

                TextColumn::make('pdf_tdr')
                    ->label('Segundo PDF')
                    ->state(
                        fn (ConvocatoriaModalidad $record): ?string =>
                            self::nombreFuente($record, 1)
                    )
                    ->url(
                        fn (ConvocatoriaModalidad $record): ?string =>
                            self::fuente($record, 1)['url_pdf'] ?? null
                    )
                    ->openUrlInNewTab()
                    ->placeholder('Sin documento')
                    ->wrap(),
            ]);
    }

    private static function fuente(
        ConvocatoriaModalidad $record,
        int $indice
    ): ?array {
        $fuentes = $record->fuentes_documentales ?? [];

        return isset($fuentes[$indice])
            && is_array($fuentes[$indice])
                ? $fuentes[$indice]
                : null;
    }

    private static function nombreFuente(
        ConvocatoriaModalidad $record,
        int $indice
    ): ?string {
        $fuente = self::fuente($record, $indice);

        if ($fuente === null) {
            return null;
        }

        return sprintf(
            '%s (pág. %s)',
            $fuente['documento'] ?? 'Documento PDF',
            $fuente['pagina'] ?? '?'
        );
    }
}
