<?php

namespace App\Filament\Docente\Pages;

use App\Models\ConvocatoriaRequisito;
use App\Models\Evidencia;
use App\Models\Propuesta;
use App\Models\PropuestaRequisito;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class RequisitosPropuesta extends Page
{
    use WithFileUploads;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Requisitos de la Propuesta';

    protected static ?string $slug =
        'requisitos-propuesta';

    protected string $view =
        'filament.docente.pages.requisitos-propuesta';

    public int $propuestaId;

    public array $cumplidos = [];

    public array $observaciones = [];

    /*
    |--------------------------------------------------------------------------
    | Formulario de evidencia
    |--------------------------------------------------------------------------
    */

    public bool $mostrarEvidencia = false;

    public ?int $requisitoEvidenciaId = null;

    public string $evidenciaNombre = '';

    public string $evidenciaDescripcion = '';

    public string $evidenciaUrl = '';

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
        $id = request()->integer(
            'propuesta'
        );

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

        /*
        |--------------------------------------------------------------------------
        | Recuperar requisitos faltantes de la convocatoria
        |--------------------------------------------------------------------------
        |
        | Normalmente ya fueron creados en Generar Propuesta.
        | Esto también protege propuestas antiguas o incompletas.
        |
        */

        if (
            $this->esEstadoEditable(
                $propuesta->estado
            )
        ) {
            $this->sincronizarRequisitos(
                $propuesta
            );
        }

        $this->cargarRequisitos();
    }

    public function guardarBorrador(): void
    {
        if (
            ! $this->guardarCambios()
        ) {
            return;
        }

        Notification::make()
            ->title('Requisitos guardados')
            ->body(
                'El cumplimiento y las observaciones se guardaron correctamente.'
            )
            ->success()
            ->send();
    }

    public function continuarAPresupuesto(): mixed
    {
        if ($this->editable()) {
            if (
                ! $this->guardarCambios()
            ) {
                return null;
            }
        }

        return redirect()->route(
            'filament.docente.pages.presupuesto-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function volverAObjetivos(): mixed
    {
        $this->propuestaDelUsuario();

        return redirect()->route(
            'filament.docente.pages.objetivos-propuesta',
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

    /*
    |--------------------------------------------------------------------------
    | Evidencias
    |--------------------------------------------------------------------------
    */

    public function abrirEvidencia(
        int $requisitoId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $this->requisitoDelUsuario(
            $requisitoId
        );

        $this->resetValidation();

        $this->requisitoEvidenciaId =
            $requisitoId;

        $this->evidenciaNombre = '';
        $this->evidenciaDescripcion = '';
        $this->evidenciaUrl = '';
        $this->archivoEvidencia = null;

        $this->mostrarEvidencia = true;
    }

    public function cerrarEvidencia(): void
    {
        $this->resetValidation();

        $this->mostrarEvidencia = false;
        $this->requisitoEvidenciaId = null;
        $this->evidenciaNombre = '';
        $this->evidenciaDescripcion = '';
        $this->evidenciaUrl = '';
        $this->archivoEvidencia = null;
    }

    public function guardarEvidencia(): void
    {
        if (! $this->editable()) {
            return;
        }

        if (
            ! $this->requisitoEvidenciaId
        ) {
            return;
        }

        $requisito =
            $this->requisitoDelUsuario(
                $this->requisitoEvidenciaId
            );

        $this->validate([
            'evidenciaNombre' => [
                'required',
                'string',
                'max:255',
            ],

            'evidenciaDescripcion' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'evidenciaUrl' => [
                'nullable',
                'url',
                'max:5000',
            ],

            'archivoEvidencia' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            ],
        ]);

        if (
            ! $this->archivoEvidencia
            && trim(
                $this->evidenciaUrl
            ) === ''
        ) {
            $this->addError(
                'archivoEvidencia',
                'Adjunta un archivo o proporciona una URL.'
            );

            return;
        }

        $ruta = null;

        try {
            if ($this->archivoEvidencia) {
                $ruta =
                    $this->archivoEvidencia
                        ->store(
                            'evidencias/propuestas/'
                            . $this->propuestaId,
                            'local'
                        );
            }

            Evidencia::create([
                'propuesta_id' =>
                    $this->propuestaId,

                'propuesta_requisito_id' =>
                    $requisito->id,

                'propuesta_entregable_id' =>
                    null,

                'nombre' =>
                    trim(
                        $this->evidenciaNombre
                    ),

                'descripcion' =>
                    $this->textoONull(
                        $this
                            ->evidenciaDescripcion
                    ),

                'ruta_archivo' =>
                    $ruta,

                'url_archivo' =>
                    $this->textoONull(
                        $this->evidenciaUrl
                    ),
            ]);
        } catch (\Throwable $e) {
            if ($ruta) {
                Storage::disk('local')
                    ->delete($ruta);
            }

            report($e);

            Notification::make()
                ->title(
                    'No se pudo guardar la evidencia'
                )
                ->danger()
                ->send();

            return;
        }

        $this->marcarEdicion();

        $this->cerrarEvidencia();

        Notification::make()
            ->title('Evidencia agregada')
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

        if (
            filled(
                $evidencia->ruta_archivo
            )
        ) {
            Storage::disk('local')
                ->delete(
                    $evidencia->ruta_archivo
                );
        }

        $evidencia->delete();

        $this->marcarEdicion();

        Notification::make()
            ->title('Evidencia eliminada')
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
                    $evidencia->ruta_archivo
                ),
            404
        );

        $extension = pathinfo(
            $evidencia->ruta_archivo,
            PATHINFO_EXTENSION
        );

        $nombre =
            Str::slug(
                $evidencia->nombre
            );

        if ($nombre === '') {
            $nombre = 'evidencia';
        }

        if ($extension !== '') {
            $nombre .= '.'
                . $extension;
        }

        return Storage::disk('local')
            ->download(
                $evidencia->ruta_archivo,
                $nombre
            );
    }

    protected function getViewData(): array
    {
        $propuesta = Propuesta::query()
            ->with([
                'convocatoria.organismo',
                'convocatoria.categoria',
            ])
            ->withCount([
                'objetivos as objetivos_generales_count' =>
                    fn ($query) =>
                        $query
                            ->where(
                                'tipo',
                                'GENERAL'
                            )
                            ->whereRaw(
                                "BTRIM(descripcion) <> ''"
                            ),

                'objetivos as objetivos_especificos_count' =>
                    fn ($query) =>
                        $query
                            ->where(
                                'tipo',
                                'ESPECIFICO'
                            )
                            ->whereRaw(
                                "BTRIM(descripcion) <> ''"
                            ),

                'itemsPresupuesto',
                'cotizaciones',
                'actividadesCronograma',
                'entregables',
            ])
            ->whereKey(
                $this->propuestaId
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();

        $requisitos =
            PropuestaRequisito::query()
                ->with([
                    'requisitoConvocatoria',
                ])
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Usamos el estado Livewire para que el progreso cambie en pantalla
        | incluso antes de pulsar Guardar.
        |--------------------------------------------------------------------------
        */

        $totalRequisitos =
            $requisitos->count();

        $cumplidos =
            $requisitos
                ->filter(
                    fn (
                        PropuestaRequisito $requisito
                    ): bool =>
                        (bool) (
                            $this->cumplidos[
                                $requisito->id
                            ]
                            ?? false
                        )
                )
                ->count();

        $pendientes =
            max(
                0,
                $totalRequisitos
                - $cumplidos
            );

        $porcentajeRequisitos =
            $totalRequisitos > 0
                ? (int) round(
                    (
                        $cumplidos
                        / $totalRequisitos
                    ) * 100
                )
                : 100;

        $objetivosCompletos =
            $propuesta
                ->objetivos_generales_count > 0
            && $propuesta
                ->objetivos_especificos_count > 0;

        $requisitosCompletos =
            $totalRequisitos === 0
            || $cumplidos
                === $totalRequisitos;

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

        $avanceDatos =
            $datosLlenos / 5;

        $presupuestoCompleto =
            $propuesta
                ->items_presupuesto_count > 0;

        $cotizacionesCompletas =
            $propuesta
                ->cotizaciones_count > 0;

        $cronogramaCompleto =
            $propuesta
                ->actividades_cronograma_count > 0;

        $entregablesCompletos =
            $propuesta
                ->entregables_count > 0;

        $puntaje =
            $avanceDatos
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

        $evidenciasPorRequisito =
            Evidencia::query()
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->whereNotNull(
                    'propuesta_requisito_id'
                )
                ->orderByDesc('id')
                ->get()
                ->groupBy(
                    'propuesta_requisito_id'
                );

        return [
            'propuesta' =>
                $propuesta,

            'convocatoria' =>
                $propuesta->convocatoria,

            'requisitos' =>
                $requisitos,

            'editable' =>
                $this->esEstadoEditable(
                    $propuesta->estado
                ),

            'totalRequisitos' =>
                $totalRequisitos,

            'cumplidosCount' =>
                $cumplidos,

            'pendientesCount' =>
                $pendientes,

            'porcentajeRequisitos' =>
                $porcentajeRequisitos,

            'evidenciasPorRequisito' =>
                $evidenciasPorRequisito,

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

    /*
    |--------------------------------------------------------------------------
    | Guardado
    |--------------------------------------------------------------------------
    */

    private function guardarCambios(): bool
    {
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

        $this->validate([
            'cumplidos' => [
                'array',
            ],

            'cumplidos.*' => [
                'boolean',
            ],

            'observaciones' => [
                'array',
            ],

            'observaciones.*' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(
            function () use (
                $propuesta
            ): void {
                $requisitos =
                    PropuestaRequisito::query()
                        ->where(
                            'propuesta_id',
                            $propuesta->id
                        )
                        ->get();

                foreach (
                    $requisitos
                    as $requisito
                ) {
                    $requisito->cumplido =
                        (bool) (
                            $this->cumplidos[
                                $requisito->id
                            ]
                            ?? false
                        );

                    $requisito->observaciones =
                        $this->textoONull(
                            (string) (
                                $this->observaciones[
                                    $requisito->id
                                ]
                                ?? ''
                            )
                        );

                    $requisito->save();
                }

                $this->marcarEdicion(
                    $propuesta
                );
            }
        );

        return true;
    }

    private function cargarRequisitos(): void
    {
        $requisitos =
            PropuestaRequisito::query()
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->orderBy('id')
                ->get();

        $this->cumplidos = [];
        $this->observaciones = [];

        foreach (
            $requisitos
            as $requisito
        ) {
            $this->cumplidos[
                $requisito->id
            ] =
                (bool)
                $requisito->cumplido;

            $this->observaciones[
                $requisito->id
            ] =
                (string) (
                    $requisito
                        ->observaciones
                    ?? ''
                );
        }
    }

    private function sincronizarRequisitos(
        Propuesta $propuesta
    ): void {
        $requisitosConvocatoria =
            ConvocatoriaRequisito::query()
                ->where(
                    'convocatoria_id',
                    $propuesta
                        ->convocatoria_id
                )
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

        foreach (
            $requisitosConvocatoria
            as $requisito
        ) {
            PropuestaRequisito::query()
                ->firstOrCreate(
                    [
                        'propuesta_id' =>
                            $propuesta->id,

                        'convocatoria_requisito_id' =>
                            $requisito->id,
                    ],
                    [
                        'cumplido' =>
                            false,

                        'observaciones' =>
                            null,
                    ]
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad
    |--------------------------------------------------------------------------
    */

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

    private function requisitoDelUsuario(
        int $requisitoId
    ): PropuestaRequisito {
        $this->propuestaDelUsuario();

        return PropuestaRequisito::query()
            ->whereKey(
                $requisitoId
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
                'propuesta_requisito_id'
            )
            ->whereHas(
                'requisito',
                fn ($query) =>
                    $query->where(
                        'propuesta_id',
                        $this->propuestaId
                    )
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
                    'Revisión de requisitos';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }
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
