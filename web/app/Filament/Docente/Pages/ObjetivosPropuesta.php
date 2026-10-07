<?php

namespace App\Filament\Docente\Pages;

use App\Models\Propuesta;
use App\Models\PropuestaObjetivo;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ObjetivosPropuesta extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Objetivos de la Propuesta';

    protected static ?string $slug =
        'objetivos-propuesta';

    protected string $view =
        'filament.docente.pages.objetivos-propuesta';

    public int $propuestaId;

    public string $objetivoGeneral = '';

    public array $objetivosEspecificos = [];

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

        $this->cargarObjetivos();
    }

    public function agregarObjetivoEspecifico(): void
    {
        if (! $this->editable()) {
            return;
        }

        $this->objetivosEspecificos[] = [
            'id' => null,
            'descripcion' => '',
            'orden' =>
                count(
                    $this->objetivosEspecificos
                ) + 2,
        ];
    }

    public function eliminarObjetivoEspecifico(
        int $indice
    ): void {
        if (! $this->editable()) {
            return;
        }

        if (
            ! array_key_exists(
                $indice,
                $this->objetivosEspecificos
            )
        ) {
            return;
        }

        $objetivo =
            $this->objetivosEspecificos[
                $indice
            ];

        if (! empty($objetivo['id'])) {
            PropuestaObjetivo::query()
                ->whereKey(
                    (int) $objetivo['id']
                )
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->where(
                    'tipo',
                    'ESPECIFICO'
                )
                ->delete();
        }

        array_splice(
            $this->objetivosEspecificos,
            $indice,
            1
        );

        $this->normalizarOrden();

        $this->guardarOrdenPersistido();

        $this->marcarEdicion();

        Notification::make()
            ->title('Objetivo eliminado')
            ->success()
            ->send();
    }

    public function subirObjetivo(
        int $indice
    ): void {
        if (
            ! $this->editable()
            || $indice <= 0
            || ! isset(
                $this->objetivosEspecificos[
                    $indice
                ]
            )
        ) {
            return;
        }

        [
            $this->objetivosEspecificos[
                $indice - 1
            ],
            $this->objetivosEspecificos[
                $indice
            ],
        ] = [
            $this->objetivosEspecificos[
                $indice
            ],
            $this->objetivosEspecificos[
                $indice - 1
            ],
        ];

        $this->normalizarOrden();

        $this->guardarOrdenPersistido();
    }

    public function bajarObjetivo(
        int $indice
    ): void {
        if (
            ! $this->editable()
            || $indice < 0
            || $indice >=
                count(
                    $this->objetivosEspecificos
                ) - 1
        ) {
            return;
        }

        [
            $this->objetivosEspecificos[
                $indice
            ],
            $this->objetivosEspecificos[
                $indice + 1
            ],
        ] = [
            $this->objetivosEspecificos[
                $indice + 1
            ],
            $this->objetivosEspecificos[
                $indice
            ],
        ];

        $this->normalizarOrden();

        $this->guardarOrdenPersistido();
    }

    public function guardarBorrador(): void
    {
        if (
            ! $this->guardarObjetivos(
                false
            )
        ) {
            return;
        }

        Notification::make()
            ->title('Objetivos guardados')
            ->body(
                'Los objetivos de la propuesta se guardaron correctamente.'
            )
            ->success()
            ->send();
    }

    public function continuarARequisitos(): mixed
    {
        if ($this->editable()) {
            if (
                ! $this->guardarObjetivos(
                    true
                )
            ) {
                return null;
            }
        }

        return redirect()->route(
            'filament.docente.pages.requisitos-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function volverAlEditor(): mixed
    {
        return redirect()->route(
            'filament.docente.pages.editor-propuesta',
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
            ->withCount([
                'requisitos',
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

        $generalGuardado =
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

        $especificosGuardados =
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
                ->count();

        $objetivosCompletos =
            $generalGuardado
            && $especificosGuardados > 0;

        $requisitosCumplidos =
            $propuesta->requisitos()
                ->where(
                    'cumplido',
                    true
                )
                ->count();

        $camposDatos = [
            $propuesta->titulo,
            $propuesta->resumen,
            $propuesta->justificacion,
            $propuesta->metodologia,
            $propuesta->impacto_esperado,
        ];

        $datosLlenos = collect(
            $camposDatos
        )
            ->filter(
                fn ($valor): bool =>
                    filled($valor)
            )
            ->count();

        $avanceDatos =
            $datosLlenos / 5;

        $requisitosCompletos =
            $propuesta->requisitos_count === 0
            || $requisitosCumplidos
                === $propuesta->requisitos_count;

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

        return [
            'propuesta' =>
                $propuesta,

            'convocatoria' =>
                $propuesta->convocatoria,

            'editable' =>
                $this->editable(),

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

    private function guardarObjetivos(
        bool $exigirCompletos
    ): bool {
        if (! $this->editable()) {
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
            'objetivoGeneral' => [
                'nullable',
                'string',
                'max:800',
            ],

            'objetivosEspecificos' => [
                'array',
            ],

            'objetivosEspecificos.*.descripcion' => [
                'nullable',
                'string',
                'max:600',
            ],
        ]);

        if (
            $exigirCompletos
            && trim(
                $this->objetivoGeneral
            ) === ''
        ) {
            $this->addError(
                'objetivoGeneral',
                'Escribe el objetivo general antes de continuar.'
            );

            return false;
        }

        $especificosNoVacios =
            collect(
                $this->objetivosEspecificos
            )
                ->filter(
                    fn (array $objetivo): bool =>
                        trim(
                            (string) (
                                $objetivo[
                                    'descripcion'
                                ] ?? ''
                            )
                        ) !== ''
                );

        if (
            $exigirCompletos
            && $especificosNoVacios
                ->isEmpty()
        ) {
            $this->addError(
                'objetivosEspecificos',
                'Agrega al menos un objetivo específico antes de continuar.'
            );

            return false;
        }

        $propuesta =
            $this->propuestaDelUsuario();

        DB::transaction(
            function () use (
                $propuesta
            ): void {
                $generalTexto =
                    trim(
                        $this->objetivoGeneral
                    );

                $generales =
                    PropuestaObjetivo::query()
                        ->where(
                            'propuesta_id',
                            $propuesta->id
                        )
                        ->where(
                            'tipo',
                            'GENERAL'
                        )
                        ->orderBy('id')
                        ->get();

                $general =
                    $generales->first();

                if ($generalTexto === '') {
                    foreach (
                        $generales
                        as $registro
                    ) {
                        $registro->delete();
                    }
                } else {
                    if (! $general) {
                        $general =
                            new PropuestaObjetivo();

                        $general->propuesta_id =
                            $propuesta->id;

                        $general->tipo =
                            'GENERAL';
                    }

                    $general->descripcion =
                        $generalTexto;

                    $general->orden = 1;

                    $general->save();

                    /*
                    | Garantizar máximo un GENERAL.
                    */
                    PropuestaObjetivo::query()
                        ->where(
                            'propuesta_id',
                            $propuesta->id
                        )
                        ->where(
                            'tipo',
                            'GENERAL'
                        )
                        ->whereKeyNot(
                            $general->id
                        )
                        ->delete();
                }

                foreach (
                    $this->objetivosEspecificos
                    as $indice => $objetivo
                ) {
                    $descripcion =
                        trim(
                            (string) (
                                $objetivo[
                                    'descripcion'
                                ] ?? ''
                            )
                        );

                    $id =
                        $objetivo['id']
                        ?? null;

                    if ($id) {
                        $registro =
                            PropuestaObjetivo::query()
                                ->whereKey(
                                    (int) $id
                                )
                                ->where(
                                    'propuesta_id',
                                    $propuesta->id
                                )
                                ->where(
                                    'tipo',
                                    'ESPECIFICO'
                                )
                                ->firstOrFail();

                        $registro->descripcion =
                            $descripcion;

                        $registro->orden =
                            $indice + 2;

                        $registro->save();

                        $this
                            ->objetivosEspecificos[
                                $indice
                            ]['id'] =
                                $registro->id;

                        continue;
                    }

                    /*
                    | No crear registros vacíos nuevos.
                    */
                    if ($descripcion === '') {
                        continue;
                    }

                    $registro =
                        PropuestaObjetivo::create([
                            'propuesta_id' =>
                                $propuesta->id,

                            'tipo' =>
                                'ESPECIFICO',

                            'descripcion' =>
                                $descripcion,

                            'orden' =>
                                $indice + 2,
                        ]);

                    $this
                        ->objetivosEspecificos[
                            $indice
                        ]['id'] =
                            $registro->id;
                }

                $this->normalizarOrden();

                $this->marcarEdicion(
                    $propuesta
                );
            }
        );

        return true;
    }

    private function cargarObjetivos(): void
    {
        $objetivos =
            PropuestaObjetivo::query()
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

        $general =
            $objetivos
                ->firstWhere(
                    'tipo',
                    'GENERAL'
                );

        $this->objetivoGeneral =
            (string) (
                $general?->descripcion
                ?? ''
            );

        $this->objetivosEspecificos =
            $objetivos
                ->where(
                    'tipo',
                    'ESPECIFICO'
                )
                ->values()
                ->map(
                    fn (
                        PropuestaObjetivo $objetivo,
                        int $indice
                    ): array => [
                        'id' =>
                            $objetivo->id,

                        'descripcion' =>
                            $objetivo->descripcion,

                        'orden' =>
                            $indice + 2,
                    ]
                )
                ->all();

        if (
            $this->objetivosEspecificos
                === []
            && $this->editable()
        ) {
            $this
                ->objetivosEspecificos[] = [
                    'id' => null,
                    'descripcion' => '',
                    'orden' => 2,
                ];
        }
    }

    private function normalizarOrden(): void
    {
        foreach (
            $this->objetivosEspecificos
            as $indice => $objetivo
        ) {
            $this
                ->objetivosEspecificos[
                    $indice
                ]['orden'] =
                    $indice + 2;
        }
    }

    private function guardarOrdenPersistido(): void
    {
        foreach (
            $this->objetivosEspecificos
            as $indice => $objetivo
        ) {
            if (empty($objetivo['id'])) {
                continue;
            }

            PropuestaObjetivo::query()
                ->whereKey(
                    (int) $objetivo['id']
                )
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->where(
                    'tipo',
                    'ESPECIFICO'
                )
                ->update([
                    'orden' =>
                        $indice + 2,
                ]);
        }

        $this->marcarEdicion();
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

    private function editable(): bool
    {
        $propuesta =
            $this->propuestaDelUsuario();

        return in_array(
            $propuesta->estado,
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
                    'Edición de objetivos';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }
    }
}
