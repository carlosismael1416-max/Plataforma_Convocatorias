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
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class EntregablesPropuesta extends Page
{
    use WithFileUploads;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Entregables de la Propuesta';

    protected static ?string $slug =
        'entregables-propuesta';

    protected string $view =
        'filament.docente.pages.entregables-propuesta';

    public const ESTADOS = [
        'PENDIENTE' => 'Pendiente',
        'EN_PROCESO' => 'En proceso',
        'ENTREGADO' => 'Entregado',
        'APROBADO' => 'Aprobado',
        'RECHAZADO' => 'Rechazado',
    ];

    public int $propuestaId;

    public ?int $entregableEditandoId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $cronogramaActividadId = '';

    public string $fechaLimite = '';

    public string $estado = 'PENDIENTE';

    public string $fechaEntrega = '';

    public $archivoEvidencia = null;

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

    public function guardarEntregable(
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
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'cronogramaActividadId' => [
                'nullable',
                'integer',

                Rule::exists(
                    'cronograma_actividades',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'propuesta_id',
                            $this->propuestaId
                        )
                ),
            ],

            'fechaLimite' => [
                'nullable',
                'date',
            ],

            'estado' => [
                'required',
                Rule::in(
                    array_keys(
                        self::ESTADOS
                    )
                ),
            ],

            'fechaEntrega' => [
                'nullable',
                'date',
            ],

            'archivoEvidencia' => [
                'nullable',
                'file',
                'max:20480',
                'mimes:pdf,docx,xlsx,zip,jpg,jpeg,png',
            ],
        ]);

        $rutaNueva = null;

        try {
            if ($this->archivoEvidencia) {
                $rutaNueva =
                    $this
                        ->archivoEvidencia
                        ->store(
                            'evidencias/propuestas/'
                            . $this->propuestaId
                            . '/entregables',
                            'local'
                        );
            }

            DB::transaction(
                function () use (
                    $propuesta,
                    $rutaNueva
                ): void {
                    if (
                        $this
                            ->entregableEditandoId
                    ) {
                        $entregable =
                            $this
                                ->entregableDelUsuario(
                                    $this
                                        ->entregableEditandoId
                                );
                    } else {
                        $entregable =
                            new PropuestaEntregable();

                        $entregable
                            ->propuesta_id =
                                $propuesta->id;
                    }

                    $entregable
                        ->cronograma_actividad_id =
                            $this
                                ->cronogramaActividadId
                            !== ''
                                ? (int)
                                    $this
                                        ->cronogramaActividadId
                                : null;

                    $entregable->nombre =
                        trim($this->nombre);

                    $entregable->descripcion =
                        $this->textoONull(
                            $this->descripcion
                        );

                    $entregable->fecha_limite =
                        $this->fechaLimite
                        !== ''
                            ? $this->fechaLimite
                            : null;

                    $entregable->estado =
                        $this->estado;

                    $entregable->fecha_entrega =
                        $this->fechaEntrega
                        !== ''
                            ? $this->fechaEntrega
                            : null;

                    $entregable->save();

                    if ($rutaNueva) {
                        $evidencia =
                            new Evidencia();

                        $evidencia->propuesta_id =
                            $propuesta->id;

                        $evidencia
                            ->propuesta_requisito_id =
                                null;

                        $evidencia
                            ->propuesta_entregable_id =
                                $entregable->id;

                        $evidencia->nombre =
                            $this
                                ->archivoEvidencia
                                ->getClientOriginalName();

                        $evidencia->descripcion =
                            'Evidencia del entregable: '
                            . $entregable->nombre;

                        $evidencia->ruta_archivo =
                            $rutaNueva;

                        $evidencia->url_archivo =
                            null;

                        $evidencia->save();
                    }
                }
            );
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('local')
                    ->delete($rutaNueva);
            }

            report($e);

            Notification::make()
                ->title(
                    'No se pudo guardar el entregable'
                )
                ->danger()
                ->send();

            return false;
        }

        $eraEdicion =
            $this->entregableEditandoId
            !== null;

        $this->marcarEdicion(
            $propuesta
        );

        $this->limpiarFormulario();

        if ($notificar) {
            Notification::make()
                ->title(
                    $eraEdicion
                        ? 'Entregable actualizado'
                        : 'Entregable agregado'
                )
                ->success()
                ->send();
        }

        return true;
    }

    public function editarEntregable(
        int $entregableId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $entregable =
            $this->entregableDelUsuario(
                $entregableId
            );

        $this->resetErrorBag();

        $this->entregableEditandoId =
            $entregable->id;

        $this->nombre =
            (string)
            $entregable->nombre;

        $this->descripcion =
            (string) (
                $entregable->descripcion
                ?? ''
            );

        $this->cronogramaActividadId =
            $entregable
                ->cronograma_actividad_id
                ? (string)
                    $entregable
                        ->cronograma_actividad_id
                : '';

        $this->fechaLimite =
            $entregable
                ->fecha_limite
                ?->format('Y-m-d')
            ?? '';

        $this->estado =
            $entregable->estado
            ?: 'PENDIENTE';

        $this->fechaEntrega =
            $entregable
                ->fecha_entrega
                ?->format(
                    'Y-m-d\TH:i'
                )
            ?? '';

        $this->archivoEvidencia =
            null;
    }

    public function cancelarEdicion(): void
    {
        $this->resetErrorBag();

        $this->limpiarFormulario();
    }

    public function eliminarEntregable(
        int $entregableId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $entregable =
            $this->entregableDelUsuario(
                $entregableId
            );

        $evidencias =
            Evidencia::query()
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->where(
                    'propuesta_entregable_id',
                    $entregable->id
                )
                ->get();

        $rutas =
            $evidencias
                ->pluck('ruta_archivo')
                ->filter()
                ->values();

        DB::transaction(
            function () use (
                $entregable
            ): void {
                Evidencia::query()
                    ->where(
                        'propuesta_id',
                        $this->propuestaId
                    )
                    ->where(
                        'propuesta_entregable_id',
                        $entregable->id
                    )
                    ->delete();

                $entregable->delete();
            }
        );

        foreach ($rutas as $ruta) {
            Storage::disk('local')
                ->delete($ruta);
        }

        if (
            $this->entregableEditandoId
            === $entregableId
        ) {
            $this->limpiarFormulario();
        }

        $this->marcarEdicion();

        Notification::make()
            ->title(
                'Entregable eliminado'
            )
            ->success()
            ->send();
    }

    public function eliminarEvidencia(
        int $evidenciaId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $evidencia =
            $this->evidenciaDelUsuario(
                $evidenciaId
            );

        $ruta =
            $evidencia->ruta_archivo;

        $evidencia->delete();

        if ($ruta) {
            Storage::disk('local')
                ->delete($ruta);
        }

        $this->marcarEdicion();

        Notification::make()
            ->title(
                'Evidencia eliminada'
            )
            ->success()
            ->send();
    }

    public function descargarEvidencia(
        int $evidenciaId
    ): mixed {
        $evidencia =
            $this->evidenciaDelUsuario(
                $evidenciaId
            );

        abort_if(
            blank(
                $evidencia->ruta_archivo
            ),
            404
        );

        abort_unless(
            Storage::disk('local')
                ->exists(
                    $evidencia
                        ->ruta_archivo
                ),
            404
        );

        return Storage::disk('local')
            ->download(
                $evidencia->ruta_archivo,
                $evidencia->nombre
            );
    }

    public function guardarBorrador(): void
    {
        if (
            $this->hayDatosFormulario()
        ) {
            if (
                ! $this->guardarEntregable(
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
                'Entregables guardados'
            )
            ->success()
            ->send();
    }

    public function volverACronograma(): mixed
    {
        $this->propuestaDelUsuario();

        return redirect()->route(
            'filament.docente.pages.cronograma-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function revisarPropuesta(): mixed
    {
        $this->propuestaDelUsuario();

        if (
            $this->hayDatosFormulario()
            && $this->editable()
        ) {
            if (
                ! $this->guardarEntregable(
                    false
                )
            ) {
                return null;
            }
        }

        $tieneEntregables =
            PropuestaEntregable::query()
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->exists();

        if (! $tieneEntregables) {
            Notification::make()
                ->title(
                    'Entregables incompletos'
                )
                ->body(
                    'Agrega al menos un entregable antes de revisar la propuesta.'
                )
                ->warning()
                ->send();

            return null;
        }

        return redirect()->route(
            'filament.docente.pages.vista-previa-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function vistaPrevia(): mixed
    {
        return $this->revisarPropuesta();
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
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

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

        $evidenciasPorEntregable =
            Evidencia::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->whereNotNull(
                    'propuesta_entregable_id'
                )
                ->orderBy('id')
                ->get()
                ->groupBy(
                    'propuesta_entregable_id'
                );

        $pendientes =
            $entregables
                ->whereIn(
                    'estado',
                    [
                        'PENDIENTE',
                        'EN_PROCESO',
                    ]
                )
                ->count();

        $entregados =
            $entregables
                ->whereIn(
                    'estado',
                    [
                        'ENTREGADO',
                        'APROBADO',
                    ]
                )
                ->count();

        $rechazados =
            $entregables
                ->where(
                    'estado',
                    'RECHAZADO'
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
            || $totalRequisitos
                === $requisitosCumplidos;

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
            $entregables->isNotEmpty();

        $camposDatos = [
            $propuesta->titulo,
            $propuesta->resumen,
            $propuesta->justificacion,
            $propuesta->metodologia,
            $propuesta->impacto_esperado,
        ];

        $datosLlenos =
            collect($camposDatos)
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

            'entregables' =>
                $entregables,

            'evidenciasPorEntregable' =>
                $evidenciasPorEntregable,

            'estados' =>
                self::ESTADOS,

            'pendientes' =>
                $pendientes,

            'entregados' =>
                $entregados,

            'rechazados' =>
                $rechazados,

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

    private function entregableDelUsuario(
        int $entregableId
    ): PropuestaEntregable {
        $this->propuestaDelUsuario();

        return PropuestaEntregable::query()
            ->whereKey(
                $entregableId
            )
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->firstOrFail();
    }

    private function evidenciaDelUsuario(
        int $evidenciaId
    ): Evidencia {
        $this->propuestaDelUsuario();

        return Evidencia::query()
            ->whereKey(
                $evidenciaId
            )
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->whereNotNull(
                'propuesta_entregable_id'
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
                    'Edición de entregables';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }
    }

    private function limpiarFormulario(): void
    {
        $this->entregableEditandoId =
            null;

        $this->nombre = '';
        $this->descripcion = '';
        $this->cronogramaActividadId = '';
        $this->fechaLimite = '';
        $this->estado = 'PENDIENTE';
        $this->fechaEntrega = '';
        $this->archivoEvidencia = null;
    }

    private function hayDatosFormulario(): bool
    {
        return $this
                ->entregableEditandoId
            !== null
            || trim($this->nombre) !== ''
            || trim($this->descripcion) !== ''
            || trim(
                $this->cronogramaActividadId
            ) !== ''
            || trim(
                $this->fechaLimite
            ) !== ''
            || trim(
                $this->fechaEntrega
            ) !== ''
            || $this->archivoEvidencia
                !== null;
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
