<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use App\Models\ConvocatoriaApoyo;
use Illuminate\Contracts\View\View;
use Filament\Support\Enums\Width;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApoyosFinancierosRelationManager extends RelationManager
{
    protected static string $relationship = 'apoyosFinancieros';

    protected static ?string $title = 'Apoyos financieros';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('concepto')
            ->columns([
                TextColumn::make('componente')
                    ->label('Componente')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'MEXICANO' => 'México',
                            'FRANCES' => 'Francia',
                            default => $state ?? 'Sin definir',
                        }
                    ),

                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('monto_maximo')
                    ->label('Importe máximo')
                    ->numeric(decimalPlaces: 2),

                TextColumn::make('moneda')
                    ->label('Moneda')
                    ->badge(),

                TextColumn::make('estado_documental')
                    ->label('Estado')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('verDetalles')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(
                        fn (ConvocatoriaApoyo $record): string =>
                            $record->concepto
                    )
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->modalContent(
                        fn (ConvocatoriaApoyo $record): View => view(
                            'filament.convocatorias.apoyo-detalles',
                            [
                                'apoyo' => $record,
                                'condicionesTexto' =>
                                    self::resumirCondiciones($record),
                            ]
                        )
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ]);
    }

    private static function primeraFuente(
        ConvocatoriaApoyo $record
    ): ?array {
        $fuentes = $record->fuentes_documentales ?? [];

        return isset($fuentes[0]) && is_array($fuentes[0])
            ? $fuentes[0]
            : null;
    }

    private static function nombreFuente(
        ConvocatoriaApoyo $record
    ): ?string {
        $fuente = self::primeraFuente($record);

        if ($fuente === null) {
            return null;
        }

        return sprintf(
            '%s (pág. %s)',
            $fuente['documento'] ?? 'Documento PDF',
            $fuente['pagina'] ?? '?'
        );
    }

    private static function resumirCondiciones(
        ConvocatoriaApoyo $record
    ): string {
        $condiciones = $record->condiciones ?? [];
        $partes = [];

        if (isset($condiciones['numero_etapas'])) {
            $partes[] = $condiciones['numero_etapas']
                . ' etapas';
        }

        if (!empty(
            $condiciones['sujeto_a_disponibilidad_presupuestal']
        )) {
            $partes[] = 'Sujeto a disponibilidad presupuestal';
        }

        $beneficiarios = $condiciones['beneficiarios'] ?? null;

        if (is_array($beneficiarios)) {
            $investigadores = (int) (
                $beneficiarios['investigadores_franceses'] ?? 0
            );

            $estudiantes = (int) (
                $beneficiarios['estudiantes_franceses'] ?? 0
            );

            if ($investigadores === 1 && $estudiantes === 1) {
                $partes[] = (
                    '1 investigador francés y '
                    . '1 estudiante francés'
                );
            }
        }

        if (
            $beneficiarios
            === 'INVESTIGADORES_FRANCESES_EN_MEXICO'
        ) {
            $partes[] = 'Investigadores franceses en México';
        }

        if (
            $beneficiarios
            === 'ESTUDIANTES_FRANCESES_EN_MEXICO'
        ) {
            $partes[] = 'Estudiantes franceses en México';
        }

        if (
            ($condiciones['trayecto'] ?? null)
            === 'FRANCIA_MEXICO_IDA_Y_VUELTA'
        ) {
            $partes[] = 'Francia–México, viaje redondo';
        }

        if (
            ($condiciones['tarifa'] ?? null)
            === 'ECONOMICA'
        ) {
            $partes[] = 'Tarifa económica';
        }

        if (
            !empty($condiciones['tope_por_persona'])
            && ($condiciones['periodicidad'] ?? null)
                === 'ANUAL'
        ) {
            $partes[] = 'Límite por persona y año';
        }

        $nombresGastos = [
            'ALIMENTACION' => 'alimentación',
            'HOSPEDAJE' => 'hospedaje',
            'TRASLADO_INTERNO' => 'traslado interno',
        ];

        $gastos = $condiciones['gastos_cubiertos'] ?? [];

        if (is_array($gastos) && $gastos !== []) {
            $partes[] = 'Cubre: ' . implode(
                ', ',
                array_map(
                    fn (string $gasto): string =>
                        $nombresGastos[$gasto] ?? $gasto,
                    $gastos
                )
            );
        }

        if (isset(
            $condiciones['duracion_indicada_dias']
        )) {
            $partes[] = sprintf(
                'Duración indicada: %d días',
                $condiciones['duracion_indicada_dias']
            );
        }

        if (isset(
            $condiciones['maximo_dias_por_anio']
        )) {
            $partes[] = sprintf(
                'Máximo %d días por año',
                $condiciones['maximo_dias_por_anio']
            );
        }

        // Compatibilidad con condiciones provisionales
        // que puedan existir en otras convocatorias.
        if (!empty(
            $condiciones['verificar_beneficiarios_y_limites']
        )) {
            $partes[] = 'Beneficiarios y límites por verificar';
        }

        if (!empty(
            $condiciones['verificar_limite_de_dias']
        )) {
            $partes[] = 'Límite de días por verificar';
        }

        return $partes
            ? implode('; ', $partes)
            : 'Sin condiciones registradas';
    }
}
