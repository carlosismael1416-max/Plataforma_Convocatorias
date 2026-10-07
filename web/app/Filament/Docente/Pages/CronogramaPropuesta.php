<?php

namespace App\Filament\Docente\Pages;

use App\Models\Cotizacion;
use App\Models\CronogramaActividad;
use App\Models\PresupuestoItem;
use App\Models\Propuesta;
use App\Models\PropuestaEntregable;
use App\Models\PropuestaObjetivo;
use App\Models\PropuestaRequisito;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CronogramaPropuesta extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Cronograma de la Propuesta';

    protected static ?string $slug =
        'cronograma-propuesta';

    protected string $view =
        'filament.docente.pages.cronograma-propuesta';

    public const ESTADOS = [
        'PENDIENTE' => 'Pendiente',
        'EN_PROCESO' => 'En proceso',
        'COMPLETADA' => 'Completada',
        'CANCELADA' => 'Cancelada',
    ];

    public int $propuestaId;

    public ?int $actividadEditandoId = null;

    public string $actividad = '';

    public string $descripcion = '';

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public string $responsable = '';

    public string $estado = 'PENDIENTE';

    public string $porcentajeAvance = '0';

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
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->propuestaId =
            $propuesta->id;
    }

    public function guardarActividad(
        bool $notificar = true
    ): bool {
        $propuesta =
            $this->propuestaDelUsuario();

        if (
            ! $this->esEstadoEditable(
                $propuesta->estado
            )
        ) {
            Notification::make()
                ->title(
                    'Propuesta de solo lectura'
                )
                ->warning()
                ->send();

            return false;
        }

        $this->resetErrorBag();

        $this->validate([
            'actividad' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'fechaInicio' => [
                'required',
                'date',
            ],

            'fechaFin' => [
                'required',
                'date',
                'after_or_equal:fechaInicio',
            ],

            'responsable' => [
                'nullable',
                'string',
                'max:255',
            ],

            'estado' => [
                'required',
                Rule::in(
                    array_keys(
                        self::ESTADOS
                    )
                ),
            ],

            'porcentajeAvance' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,2',
            ],
        ], [
            'fechaFin.after_or_equal' =>
                'La fecha final no puede ser anterior a la fecha de inicio.',
        ]);

        if ($this->actividadEditandoId) {
            $registro =
                $this->actividadDelUsuario(
                    $this->actividadEditandoId
                );
        } else {
            $registro =
                new CronogramaActividad();

            $registro->propuesta_id =
                $propuesta->id;

            $registro->orden =
                (
                    CronogramaActividad::query()
                        ->where(
                            'propuesta_id',
                            $propuesta->id
                        )
                        ->max('orden')
                    ?? 0
                ) + 1;
        }

        $registro->actividad =
            trim($this->actividad);

        $registro->descripcion =
            $this->textoONull(
                $this->descripcion
            );

        $registro->fecha_inicio =
            $this->fechaInicio;

        $registro->fecha_fin =
            $this->fechaFin;

        $registro->responsable =
            $this->textoONull(
                $this->responsable
            );

        $registro->estado =
            $this->estado;

        $registro->porcentaje_avance =
            round(
                (float)
                $this->porcentajeAvance,
                2
            );

        $registro->save();

        $eraEdicion =
            $this->actividadEditandoId !== null;

        $this->normalizarOrden();

        $this->marcarEdicion(
            $propuesta
        );

        $this->limpiarFormulario();

        if ($notificar) {
            Notification::make()
                ->title(
                    $eraEdicion
                        ? 'Actividad actualizada'
                        : 'Actividad agregada'
                )
                ->success()
                ->send();
        }

        return true;
    }

    public function editarActividad(
        int $actividadId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $registro =
            $this->actividadDelUsuario(
                $actividadId
            );

        $this->resetErrorBag();

        $this->actividadEditandoId =
            $registro->id;

        $this->actividad =
            (string)
            $registro->actividad;

        $this->descripcion =
            (string) (
                $registro->descripcion
                ?? ''
            );

        $this->fechaInicio =
            $registro
                ->fecha_inicio
                ?->format('Y-m-d')
            ?? '';

        $this->fechaFin =
            $registro
                ->fecha_fin
                ?->format('Y-m-d')
            ?? '';

        $this->responsable =
            (string) (
                $registro->responsable
                ?? ''
            );

        $this->estado =
            $registro->estado
            ?: 'PENDIENTE';

        $this->porcentajeAvance =
            $this->numeroFormulario(
                $registro->porcentaje_avance
            );
    }

    public function cancelarEdicion(): void
    {
        $this->resetErrorBag();

        $this->limpiarFormulario();
    }

    public function eliminarActividad(
        int $actividadId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $registro =
            $this->actividadDelUsuario(
                $actividadId
            );

        /*
        |--------------------------------------------------------------------------
        | Protección de entregables relacionados
        |--------------------------------------------------------------------------
        */

        if (
            $registro->entregables()
                ->exists()
        ) {
            Notification::make()
                ->title(
                    'No se puede eliminar'
                )
                ->body(
                    'Esta actividad tiene entregables relacionados. Elimina o reasigna primero esos entregables.'
                )
                ->warning()
                ->send();

            return;
        }

        $registro->delete();

        if (
            $this->actividadEditandoId
            === $actividadId
        ) {
            $this->limpiarFormulario();
        }

        $this->normalizarOrden();

        $this->marcarEdicion();

        Notification::make()
            ->title(
                'Actividad eliminada'
            )
            ->success()
            ->send();
    }

    public function moverArriba(
        int $actividadId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $this->moverActividad(
            $actividadId,
            -1
        );
    }

    public function moverAbajo(
        int $actividadId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $this->moverActividad(
            $actividadId,
            1
        );
    }

    public function guardarBorrador(): void
    {
        if (
            $this->hayDatosFormulario()
        ) {
            if (
                ! $this->guardarActividad(
                    false
                )
            ) {
                return;
            }
        } else {
            $propuesta =
                $this->propuestaDelUsuario();

            if (
                ! $this->esEstadoEditable(
                    $propuesta->estado
                )
            ) {
                return;
            }

            $this->marcarEdicion(
                $propuesta
            );
        }

        Notification::make()
            ->title(
                'Cronograma guardado'
            )
            ->success()
            ->send();
    }

    public function volverACotizaciones(): mixed
    {
        $this->propuestaDelUsuario();

        return redirect()->route(
            'filament.docente.pages.cotizaciones-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function continuarAEntregables(): mixed
    {
        $this->propuestaDelUsuario();

        if (
            $this->hayDatosFormulario()
            && $this->editable()
        ) {
            if (
                ! $this->guardarActividad(
                    false
                )
            ) {
                return null;
            }
        }

        $tieneActividades =
            CronogramaActividad::query()
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->exists();

        if (! $tieneActividades) {
            Notification::make()
                ->title(
                    'Cronograma incompleto'
                )
                ->body(
                    'Agrega al menos una actividad antes de continuar.'
                )
                ->warning()
                ->send();

            return null;
        }

        return redirect()->route(
            'filament.docente.pages.entregables-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function vistaPrevia(): mixed
    {
        $this->propuestaDelUsuario();

        return redirect()->route(
            'filament.docente.pages.vista-previa-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    protected function getViewData(): array
    {
        $propuesta = Propuesta::query()
            ->with([
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

        $actividades =
            CronogramaActividad::query()
                ->withCount(
                    'entregables'
                )
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Resumen del cronograma
        |--------------------------------------------------------------------------
        */

        $inicioProyecto =
            $actividades
                ->min('fecha_inicio');

        $finProyecto =
            $actividades
                ->max('fecha_fin');

        $duracionDias = null;

        if (
            $inicioProyecto
            && $finProyecto
        ) {
            $duracionDias =
                Carbon::parse(
                    $inicioProyecto
                )
                    ->startOfDay()
                    ->diffInDays(
                        Carbon::parse(
                            $finProyecto
                        )->startOfDay()
                    ) + 1;
        }

        $actividadesParaAvance =
            $actividades
                ->where(
                    'estado',
                    '!=',
                    'CANCELADA'
                );

        $avancePromedio =
            $actividadesParaAvance
                ->isNotEmpty()
                ? round(
                    $actividadesParaAvance
                        ->avg(
                            fn (
                                CronogramaActividad $actividad
                            ): float =>
                                (float)
                                $actividad
                                    ->porcentaje_avance
                        ),
                    2
                )
                : 0;

        $completadas =
            $actividades
                ->where(
                    'estado',
                    'COMPLETADA'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Progreso general de la propuesta
        |--------------------------------------------------------------------------
        */

        $objetivoGeneral =
            PropuestaObjetivo::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->where(
                    'tipo',
                    'GENERAL'
                )
                ->whereRaw(
                    "BTRIM(descripcion) <> ''"
                )
                ->exists();

        $objetivoEspecifico =
            PropuestaObjetivo::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->where(
                    'tipo',
                    'ESPECIFICO'
                )
                ->whereRaw(
                    "BTRIM(descripcion) <> ''"
                )
                ->exists();

        $objetivosCompletos =
            $objetivoGeneral
            && $objetivoEspecifico;

        $totalRequisitos =
            PropuestaRequisito::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->count();

        $requisitosCumplidos =
            PropuestaRequisito::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->where(
                    'cumplido',
                    true
                )
                ->count();

        $requisitosCompletos =
            $totalRequisitos === 0
            || $requisitosCumplidos
                === $totalRequisitos;

        $presupuestoCompleto =
            PresupuestoItem::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->exists();

        $cotizacionesCompletas =
            Cotizacion::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->exists();

        $cronogramaCompleto =
            $actividades->isNotEmpty();

        $entregablesCompletos =
            PropuestaEntregable::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->exists();

        $camposDatos = [
            $propuesta->titulo,
            $propuesta->resumen,
            $propuesta->justificacion,
            $propuesta->metodologia,
            $propuesta->impacto_esperado,
        ];

        $datosLlenos =
            collect(
                $camposDatos
            )
                ->filter(
                    fn ($valor): bool =>
                        filled($valor)
                )
                ->count();

        $puntaje =
            ($datosLlenos / 5)
            + ($objetivosCompletos ? 1 : 0)
            + ($requisitosCompletos ? 1 : 0)
            + ($presupuestoCompleto ? 1 : 0)
            + ($cotizacionesCompletas ? 1 : 0)
            + ($cronogramaCompleto ? 1 : 0)
            + ($entregablesCompletos ? 1 : 0);

        $progreso = min(
            100,
            (int) round(
                ($puntaje / 7) * 100
            )
        );

        return [
            'propuesta' =>
                $propuesta,

            'actividades' =>
                $actividades,

            'estados' =>
                self::ESTADOS,

            'inicioProyecto' =>
                $inicioProyecto,

            'finProyecto' =>
                $finProyecto,

            'duracionDias' =>
                $duracionDias,

            'avancePromedio' =>
                $avancePromedio,

            'completadas' =>
                $completadas,

            'editable' =>
                $this->esEstadoEditable(
                    $propuesta->estado
                ),

            'progreso' =>
                $progreso,

            'pasos' => [
                'datos' =>
                    $datosLlenos === 5,

                'objetivos' =>
                    $objetivosCompletos,

                'requisitos' =>
                    $requisitosCompletos,

                'presupuesto' =>
                    $presupuestoCompleto,

                'cotizaciones' =>
                    $cotizacionesCompletas,

                'cronograma' =>
                    $cronogramaCompleto,

                'entregables' =>
                    $entregablesCompletos,
            ],
        ];
    }

    private function propuestaDelUsuario(): Propuesta
    {
        return Propuesta::query()
            ->whereKey(
                $this->propuestaId
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();
    }

    private function actividadDelUsuario(
        int $actividadId
    ): CronogramaActividad {
        $this->propuestaDelUsuario();

        return CronogramaActividad::query()
            ->whereKey(
                $actividadId
            )
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->firstOrFail();
    }

    private function editable(): bool
    {
        return $this->esEstadoEditable(
            $this
                ->propuestaDelUsuario()
                ->estado
        );
    }

    private function esEstadoEditable(
        string $estado
    ): bool {
        return in_array(
            $estado,
            [
                'BORRADOR',
                'GENERADA',
                'EDITANDO',
                'LISTA',
            ],
            true
        );
    }

    private function moverActividad(
        int $actividadId,
        int $direccion
    ): void {
        $this->actividadDelUsuario(
            $actividadId
        );

        DB::transaction(
            function () use (
                $actividadId,
                $direccion
            ): void {
                $actividades =
                    CronogramaActividad::query()
                        ->where(
                            'propuesta_id',
                            $this->propuestaId
                        )
                        ->orderBy('orden')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                /*
                | Primero normalizamos:
                | 1, 2, 3, 4...
                */
                foreach (
                    $actividades
                    as $indice => $actividad
                ) {
                    $nuevoOrden =
                        $indice + 1;

                    if (
                        $actividad->orden
                        !== $nuevoOrden
                    ) {
                        $actividad->orden =
                            $nuevoOrden;

                        $actividad->save();
                    }
                }

                $indiceActual =
                    $actividades
                        ->search(
                            fn (
                                CronogramaActividad $actividad
                            ): bool =>
                                $actividad->id
                                === $actividadId
                        );

                if (
                    $indiceActual === false
                ) {
                    return;
                }

                $indiceDestino =
                    $indiceActual
                    + $direccion;

                if (
                    $indiceDestino < 0
                    || $indiceDestino
                        >= $actividades->count()
                ) {
                    return;
                }

                $actual =
                    $actividades[
                        $indiceActual
                    ];

                $destino =
                    $actividades[
                        $indiceDestino
                    ];

                $ordenActual =
                    $actual->orden;

                $actual->orden =
                    $destino->orden;

                $destino->orden =
                    $ordenActual;

                $actual->save();
                $destino->save();
            }
        );

        $this->marcarEdicion();
    }

    private function normalizarOrden(): void
    {
        CronogramaActividad::query()
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->each(
                function (
                    CronogramaActividad $actividad,
                    int $indice
                ): void {
                    $orden =
                        $indice + 1;

                    if (
                        $actividad->orden
                        !== $orden
                    ) {
                        $actividad->orden =
                            $orden;

                        $actividad->save();
                    }
                }
            );
    }

    private function marcarEdicion(
        ?Propuesta $propuesta = null
    ): void {
        $propuesta ??=
            $this->propuestaDelUsuario();

        if (
            in_array(
                $propuesta->estado,
                [
                    'BORRADOR',
                    'GENERADA',
                    'LISTA',
                ],
                true
            )
        ) {
            $propuesta->estado =
                'EDITANDO';

            $propuesta->save();
        } else {
            $propuesta->touch();
        }

        $seguimiento =
            UsuarioConvocatoria::query()
                ->where(
                    'user_id',
                    auth()->id()
                )
                ->where(
                    'convocatoria_id',
                    $propuesta
                        ->convocatoria_id
                )
                ->first();

        if ($seguimiento) {
            if (
                in_array(
                    $seguimiento
                        ->estado_seguimiento,
                    [
                        'GUARDADA',
                        'REVISANDO',
                    ],
                    true
                )
            ) {
                $seguimiento
                    ->estado_seguimiento =
                        'PREPARANDO_PROPUESTA';
            }

            $seguimiento
                ->ultima_accion =
                    'Edición de cronograma';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }
    }

    private function limpiarFormulario(): void
    {
        $this->actividadEditandoId =
            null;

        $this->actividad = '';
        $this->descripcion = '';
        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->responsable = '';
        $this->estado = 'PENDIENTE';
        $this->porcentajeAvance = '0';
    }

    private function hayDatosFormulario(): bool
    {
        return $this
                ->actividadEditandoId
            !== null
            || trim(
                $this->actividad
            ) !== ''
            || trim(
                $this->descripcion
            ) !== ''
            || trim(
                $this->fechaInicio
            ) !== ''
            || trim(
                $this->fechaFin
            ) !== ''
            || trim(
                $this->responsable
            ) !== ''
            || (
                trim(
                    $this->porcentajeAvance
                ) !== ''
                && (float)
                    $this->porcentajeAvance > 0
            );
    }

    private function numeroFormulario(
        mixed $valor
    ): string {
        return rtrim(
            rtrim(
                number_format(
                    (float) $valor,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }

    private function textoONull(
        string $texto
    ): ?string {
        $texto = trim($texto);

        return $texto !== ''
            ? $texto
            : null;
    }
}
