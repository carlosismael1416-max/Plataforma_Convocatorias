<?php

namespace App\Filament\Docente\Pages;

use App\Models\Cotizacion;
use App\Models\CronogramaActividad;
use App\Models\Evidencia;
use App\Models\PresupuestoItem;
use App\Models\Propuesta;
use App\Models\PropuestaEntregable;
use App\Models\PropuestaObjetivo;
use App\Models\PropuestaRequisito;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class VistaPreviaPropuesta extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Vista Previa de la Propuesta';

    protected static ?string $slug =
        'vista-previa-propuesta';

    protected string $view =
        'filament.docente.pages.vista-previa-propuesta';

    public int $propuestaId;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function mount(): void
    {
        $id = request()->integer('propuesta');

        abort_if(
            $id <= 0,
            404,
            'Propuesta no especificada.'
        );

        $propuesta = Propuesta::query()
            ->whereKey($id)
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();

        $this->propuestaId =
            $propuesta->id;
    }

    public function descargarEvidencia(
        int $evidenciaId
    ): mixed {
        $evidencia = Evidencia::query()
            ->whereKey($evidenciaId)
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->firstOrFail();

        abort_if(
            blank($evidencia->ruta_archivo),
            404
        );

        abort_unless(
            Storage::disk('local')
                ->exists(
                    $evidencia->ruta_archivo
                ),
            404
        );

        return Storage::disk('local')
            ->download(
                $evidencia->ruta_archivo,
                $evidencia->nombre
            );
    }

    public function descargarCotizacion(
        int $cotizacionId
    ): mixed {
        $cotizacion = Cotizacion::query()
            ->whereKey($cotizacionId)
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->firstOrFail();

        $ruta =
            $cotizacion->archivo_url;

        abort_if(
            blank($ruta),
            404
        );

        abort_if(
            str_starts_with(
                strtolower($ruta),
                'http://'
            )
            || str_starts_with(
                strtolower($ruta),
                'https://'
            ),
            404
        );

        abort_unless(
            Storage::disk('local')
                ->exists($ruta),
            404
        );

        return Storage::disk('local')
            ->download(
                $ruta,
                basename($ruta)
            );
    }

    public function exportarPdf(): mixed
    {
        $data = $this->getViewData();

        $propuesta =
            $data['propuesta'];

        $nombre =
            'propuesta_'
            . $propuesta->id
            . '_v'
            . $propuesta->version
            . '.pdf';

        return Pdf::loadView(
            'pdf.propuesta',
            $data
        )
            ->setPaper(
                'a4',
                'portrait'
            )
            ->download($nombre);
    }

    public function exportarExcel(): mixed
    {
        $data = $this->getViewData();

        $propuesta =
            $data['propuesta'];

        $directorio =
            storage_path(
                'app/tmp'
            );

        File::ensureDirectoryExists(
            $directorio
        );

        $ruta =
            $directorio
            . '/propuesta_'
            . $propuesta->id
            . '_'
            . Str::uuid()
            . '.xlsx';

        $writer =
            new Writer();

        try {
            $writer->openToFile(
                $ruta
            );

            $fila =
                function (
                    array $valores
                ) use (
                    $writer
                ): void {
                    $writer->addRow(
                        Row::fromValues(
                            array_map(
                                fn ($valor) =>
                                    $this
                                        ->valorSeguroExcel(
                                            $valor
                                        ),
                                $valores
                            )
                        )
                    );
                };

            /*
            |--------------------------------------------------------------------------
            | Encabezado
            |--------------------------------------------------------------------------
            */

            $fila([
                'PROPUESTA ITSVA',
            ]);

            $fila([
                'ID',
                $propuesta->id,
            ]);

            $fila([
                'Título',
                $propuesta->titulo,
            ]);

            $fila([
                'Convocatoria',
                $propuesta
                    ->convocatoria
                    ?->titulo
                ?? '',
            ]);

            $fila([
                'Organismo',
                $propuesta
                    ->convocatoria
                    ?->organismo
                    ?->nombre
                ?? '',
            ]);

            $fila([
                'Área temática',
                $propuesta
                    ->convocatoria
                    ?->categoria
                    ?->nombre
                ?? '',
            ]);

            $fila([
                'Responsable',
                $data[
                    'responsable'
                ],
            ]);

            $fila([
                'Estado',
                $propuesta->estado,
            ]);

            $fila([
                'Versión',
                $propuesta->version,
            ]);

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Contenido de la propuesta
            |--------------------------------------------------------------------------
            */

            $fila([
                'DATOS DE LA PROPUESTA',
            ]);

            $fila([
                'Resumen',
                $propuesta->resumen
                ?? '',
            ]);

            $fila([
                'Justificación',
                $propuesta
                    ->justificacion
                ?? '',
            ]);

            $fila([
                'Metodología',
                $propuesta
                    ->metodologia
                ?? '',
            ]);

            $fila([
                'Impacto esperado',
                $propuesta
                    ->impacto_esperado
                ?? '',
            ]);

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Objetivos
            |--------------------------------------------------------------------------
            */

            $fila([
                'OBJETIVOS',
            ]);

            $fila([
                'Tipo',
                'Descripción',
                'Orden',
            ]);

            if (
                $data[
                    'objetivoGeneral'
                ]
            ) {
                $fila([
                    'GENERAL',
                    $data[
                        'objetivoGeneral'
                    ]->descripcion,
                    $data[
                        'objetivoGeneral'
                    ]->orden,
                ]);
            }

            foreach (
                $data[
                    'objetivosEspecificos'
                ]
                as $objetivo
            ) {
                $fila([
                    'ESPECÍFICO',
                    $objetivo
                        ->descripcion,
                    $objetivo->orden,
                ]);
            }

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Requisitos
            |--------------------------------------------------------------------------
            */

            $fila([
                'REQUISITOS',
            ]);

            $fila([
                'Requisito',
                'Cumplido',
                'Observaciones',
            ]);

            foreach (
                $data['requisitos']
                as $requisito
            ) {
                $fila([
                    $requisito
                        ->requisitoConvocatoria
                        ?->titulo
                    ?? 'Requisito',

                    $requisito->cumplido
                        ? 'Sí'
                        : 'No',

                    $requisito
                        ->observaciones
                    ?? '',
                ]);
            }

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Presupuesto
            |--------------------------------------------------------------------------
            */

            $fila([
                'PRESUPUESTO',
            ]);

            $fila([
                'Concepto',
                'Descripción',
                'Categoría',
                'Cantidad',
                'Precio unitario',
                'Subtotal',
                'Moneda',
            ]);

            foreach (
                $data['presupuesto']
                as $item
            ) {
                $cantidad =
                    (float)
                    $item->cantidad;

                $precio =
                    (float)
                    $item
                        ->precio_unitario;

                $fila([
                    $item->concepto,

                    $item->descripcion
                    ?? '',

                    $item
                        ->categoria_gasto
                    ?? '',

                    $cantidad,

                    $precio,

                    round(
                        $cantidad
                        * $precio,
                        2
                    ),

                    $item->moneda
                    ?: 'MXN',
                ]);
            }

            foreach (
                $data[
                    'totalesPorMoneda'
                ]
                as $moneda => $total
            ) {
                $fila([
                    'TOTAL',
                    '',
                    '',
                    '',
                    '',
                    (float) $total,
                    $moneda,
                ]);
            }

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Cotizaciones
            |--------------------------------------------------------------------------
            */

            $fila([
                'COTIZACIONES',
            ]);

            $fila([
                'Concepto',
                'Proveedor',
                'Monto',
                'Moneda',
                'Fecha',
                'Archivo',
            ]);

            foreach (
                $data['cotizaciones']
                as $cotizacion
            ) {
                $fila([
                    $cotizacion
                        ->concepto
                    ?? '',

                    $cotizacion
                        ->proveedor
                    ?? '',

                    (float)
                    $cotizacion->monto,

                    $cotizacion->moneda,

                    $cotizacion
                        ->fecha_cotizacion
                        ?->format(
                            'Y-m-d'
                        )
                    ?? '',

                    filled(
                        $cotizacion
                            ->archivo_url
                    )
                        ? 'Disponible'
                        : 'Pendiente',
                ]);
            }

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Cronograma
            |--------------------------------------------------------------------------
            */

            $fila([
                'CRONOGRAMA',
            ]);

            $fila([
                'Orden',
                'Actividad',
                'Descripción',
                'Inicio',
                'Fin',
                'Responsable',
                'Estado',
                'Avance %',
            ]);

            foreach (
                $data['actividades']
                as $actividad
            ) {
                $fila([
                    $actividad->orden,

                    $actividad
                        ->actividad,

                    $actividad
                        ->descripcion
                    ?? '',

                    $actividad
                        ->fecha_inicio
                        ?->format(
                            'Y-m-d'
                        )
                    ?? '',

                    $actividad
                        ->fecha_fin
                        ?->format(
                            'Y-m-d'
                        )
                    ?? '',

                    $actividad
                        ->responsable
                    ?? '',

                    $actividad->estado,

                    (float)
                    $actividad
                        ->porcentaje_avance,
                ]);
            }

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Entregables
            |--------------------------------------------------------------------------
            */

            $fila([
                'ENTREGABLES',
            ]);

            $fila([
                'Nombre',
                'Descripción',
                'Actividad relacionada',
                'Fecha límite',
                'Estado',
                'Fecha entrega',
            ]);

            foreach (
                $data['entregables']
                as $entregable
            ) {
                $fila([
                    $entregable->nombre,

                    $entregable
                        ->descripcion
                    ?? '',

                    $entregable
                        ->actividad
                        ?->actividad
                    ?? '',

                    $entregable
                        ->fecha_limite
                        ?->format(
                            'Y-m-d'
                        )
                    ?? '',

                    $entregable->estado,

                    $entregable
                        ->fecha_entrega
                        ?->format(
                            'Y-m-d H:i:s'
                        )
                    ?? '',
                ]);
            }

            $fila([]);

            /*
            |--------------------------------------------------------------------------
            | Evidencias
            |--------------------------------------------------------------------------
            */

            $fila([
                'EVIDENCIAS',
            ]);

            $fila([
                'Nombre',
                'Descripción',
                'Asociación',
                'Estado del archivo',
            ]);

            foreach (
                $data['evidencias']
                as $evidencia
            ) {
                $asociacion =
                    'General';

                if (
                    $evidencia
                        ->propuesta_requisito_id
                ) {
                    $asociacion =
                        'Requisito #'
                        . $evidencia
                            ->propuesta_requisito_id;
                }

                if (
                    $evidencia
                        ->propuesta_entregable_id
                ) {
                    $asociacion =
                        'Entregable #'
                        . $evidencia
                            ->propuesta_entregable_id;
                }

                $fila([
                    $evidencia->nombre,

                    $evidencia
                        ->descripcion
                    ?? '',

                    $asociacion,

                    filled(
                        $evidencia
                            ->ruta_archivo
                    )
                    || filled(
                        $evidencia
                            ->url_archivo
                    )
                        ? 'Disponible'
                        : 'Sin archivo',
                ]);
            }

            $writer->close();
        } catch (\Throwable $e) {
            try {
                $writer->close();
            } catch (\Throwable) {
                //
            }

            if (
                is_file($ruta)
            ) {
                @unlink($ruta);
            }

            throw $e;
        }

        $nombre =
            'propuesta_'
            . $propuesta->id
            . '_v'
            . $propuesta->version
            . '.xlsx';

        return response()
            ->download(
                $ruta,
                $nombre,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(
                true
            );
    }

    private function valorSeguroExcel(
        mixed $valor
    ): string|int|float
    {
        if ($valor === null) {
            return '';
        }

        if (
            is_int($valor)
            || is_float($valor)
        ) {
            return $valor;
        }

        if (is_bool($valor)) {
            return $valor
                ? 'Sí'
                : 'No';
        }

        $texto =
            (string) $valor;

        /*
        | Evita que contenido escrito por el usuario
        | sea interpretado como fórmula por Excel.
        */
        if (
            preg_match(
                '/^[=+\-@]/u',
                $texto
            ) === 1
        ) {
            return "'"
                . $texto;
        }

        return $texto;
    }

    protected function getViewData(): array
    {
        $propuesta =
            Propuesta::query()
                ->with([
                    'usuario',
                    'convocatoria.organismo',
                    'convocatoria.categoria',
                ])
                ->whereKey(
                    $this->propuestaId
                )
                ->where(
                    'user_id',
                    auth()->id()
                )
                ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Objetivos
        |--------------------------------------------------------------------------
        */

        $objetivos =
            PropuestaObjetivo::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

        $objetivoGeneral =
            $objetivos->first(
                fn (
                    PropuestaObjetivo $objetivo
                ): bool =>
                    $objetivo->tipo
                    === 'GENERAL'
            );

        $objetivosEspecificos =
            $objetivos
                ->filter(
                    fn (
                        PropuestaObjetivo $objetivo
                    ): bool =>
                        $objetivo->tipo
                        === 'ESPECIFICO'
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Requisitos
        |--------------------------------------------------------------------------
        */

        $requisitos =
            PropuestaRequisito::query()
                ->with(
                    'requisitoConvocatoria'
                )
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Presupuesto
        |--------------------------------------------------------------------------
        */

        $presupuesto =
            PresupuestoItem::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('id')
                ->get();

        $totalesPorMoneda =
            $presupuesto
                ->groupBy(
                    fn (
                        PresupuestoItem $item
                    ): string =>
                        strtoupper(
                            $item->moneda
                            ?: (
                                $propuesta
                                    ->convocatoria
                                    ?->moneda
                                ?: 'MXN'
                            )
                        )
                )
                ->map(
                    fn ($items): float =>
                        round(
                            $items->sum(
                                fn (
                                    PresupuestoItem $item
                                ): float =>
                                    (float)
                                    $item->cantidad
                                    *
                                    (float)
                                    $item
                                        ->precio_unitario
                            ),
                            2
                        )
                );

        /*
        |--------------------------------------------------------------------------
        | Cotizaciones
        |--------------------------------------------------------------------------
        */

        $cotizaciones =
            Cotizacion::query()
                ->with(
                    'presupuestoItem'
                )
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderByDesc(
                    'fecha_cotizacion'
                )
                ->orderByDesc('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Cronograma
        |--------------------------------------------------------------------------
        */

        $actividades =
            CronogramaActividad::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

        $inicioProyecto =
            $actividades
                ->sortBy('fecha_inicio')
                ->first()
                ?->fecha_inicio;

        $finProyecto =
            $actividades
                ->sortByDesc('fecha_fin')
                ->first()
                ?->fecha_fin;

        $duracionDias = null;

        if (
            $inicioProyecto
            && $finProyecto
        ) {
            $duracionDias =
                $inicioProyecto
                    ->copy()
                    ->startOfDay()
                    ->diffInDays(
                        $finProyecto
                            ->copy()
                            ->startOfDay()
                    )
                + 1;
        }

        /*
        |--------------------------------------------------------------------------
        | Entregables
        |--------------------------------------------------------------------------
        */

        $entregables =
            PropuestaEntregable::query()
                ->with('actividad')
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('fecha_limite')
                ->orderBy('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Evidencias
        |--------------------------------------------------------------------------
        */

        $evidencias =
            Evidencia::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('id')
                ->get();

        $evidenciasPorRequisito =
            $evidencias
                ->whereNotNull(
                    'propuesta_requisito_id'
                )
                ->groupBy(
                    'propuesta_requisito_id'
                );

        $evidenciasPorEntregable =
            $evidencias
                ->whereNotNull(
                    'propuesta_entregable_id'
                )
                ->groupBy(
                    'propuesta_entregable_id'
                );

        /*
        |--------------------------------------------------------------------------
        | Responsable
        |--------------------------------------------------------------------------
        */

        $responsable =
            trim(
                (string) (
                    $propuesta
                        ->usuario
                        ?->name
                    ?? ''
                )
                . ' '
                . (string) (
                    $propuesta
                        ->usuario
                        ?->apellidos
                    ?? ''
                )
            );

        if ($responsable === '') {
            $responsable =
                'Docente ITSVA';
        }

        /*
        |--------------------------------------------------------------------------
        | Completitud principal
        |--------------------------------------------------------------------------
        |
        | Cotizaciones no bloquean la propuesta porque
        | pueden no aplicar a todos los proyectos.
        |--------------------------------------------------------------------------
        */

        $datosCompletos =
            collect([
                $propuesta->titulo,
                $propuesta->resumen,
                $propuesta->justificacion,
                $propuesta->metodologia,
                $propuesta->impacto_esperado,
            ])
                ->every(
                    fn ($valor): bool =>
                        filled($valor)
                );

        $objetivosCompletos =
            $objetivoGeneral
                !== null
            && $objetivosEspecificos
                ->isNotEmpty();

        $requisitosCompletos =
            $requisitos->isEmpty()
            || $requisitos->every(
                fn (
                    PropuestaRequisito $requisito
                ): bool =>
                    (bool)
                    $requisito->cumplido
            );

        $presupuestoCompleto =
            $presupuesto->isNotEmpty();

        $cronogramaCompleto =
            $actividades->isNotEmpty();

        $entregablesCompletos =
            $entregables->isNotEmpty();

        $seccionesPrincipales = [
            'datos' =>
                $datosCompletos,

            'objetivos' =>
                $objetivosCompletos,

            'requisitos' =>
                $requisitosCompletos,

            'presupuesto' =>
                $presupuestoCompleto,

            'cronograma' =>
                $cronogramaCompleto,

            'entregables' =>
                $entregablesCompletos,
        ];

        $progreso =
            (int) round(
                collect(
                    $seccionesPrincipales
                )
                    ->filter()
                    ->count()
                / count(
                    $seccionesPrincipales
                )
                * 100
            );

        $listaParaRevision =
            ! in_array(
                false,
                $seccionesPrincipales,
                true
            );

        return [
            'propuesta' =>
                $propuesta,

            'responsable' =>
                $responsable,

            'objetivoGeneral' =>
                $objetivoGeneral,

            'objetivosEspecificos' =>
                $objetivosEspecificos,

            'requisitos' =>
                $requisitos,

            'presupuesto' =>
                $presupuesto,

            'totalesPorMoneda' =>
                $totalesPorMoneda,

            'cotizaciones' =>
                $cotizaciones,

            'actividades' =>
                $actividades,

            'inicioProyecto' =>
                $inicioProyecto,

            'finProyecto' =>
                $finProyecto,

            'duracionDias' =>
                $duracionDias,

            'entregables' =>
                $entregables,

            'evidencias' =>
                $evidencias,

            'evidenciasPorRequisito' =>
                $evidenciasPorRequisito,

            'evidenciasPorEntregable' =>
                $evidenciasPorEntregable,

            'progreso' =>
                $progreso,

            'listaParaRevision' =>
                $listaParaRevision,

            'seccionesPrincipales' =>
                $seccionesPrincipales,
        ];
    }
}
