<?php

namespace App\Filament\Docente\Pages;

use App\Models\PresupuestoItem;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;

class PresupuestoPropuesta extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Presupuesto de la Propuesta';

    protected static ?string $slug =
        'presupuesto-propuesta';

    protected string $view =
        'filament.docente.pages.presupuesto-propuesta';

    public const CATEGORIAS = [
        'EQUIPO' => 'Equipo',
        'MATERIALES' => 'Materiales',
        'SERVICIOS' => 'Servicios',
        'VIATICOS' => 'Viáticos',
        'PERSONAL' => 'Personal',
        'INFRAESTRUCTURA' => 'Infraestructura',
        'OTRO' => 'Otro',
    ];

    public int $propuestaId;

    public ?int $itemEditandoId = null;

    public string $concepto = '';

    public string $descripcion = '';

    public string $categoriaGasto = 'EQUIPO';

    public string $cantidad = '1';

    public string $precioUnitario = '';

    public string $moneda = 'MXN';

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
            ->with('convocatoria')
            ->whereKey($id)
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();

        $this->propuestaId =
            $propuesta->id;

        $this->moneda =
            strtoupper(
                trim(
                    (string) (
                        $propuesta
                            ->convocatoria
                            ?->moneda
                        ?: 'MXN'
                    )
                )
            );
    }

    public function guardarConcepto(
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
            'concepto' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'categoriaGasto' => [
                'required',
                Rule::in(
                    array_keys(
                        self::CATEGORIAS
                    )
                ),
            ],

            'cantidad' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'decimal:0,2',
            ],

            'precioUnitario' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999999.99',
                'decimal:0,2',
            ],
        ]);

        $cantidad =
            round(
                (float) $this->cantidad,
                2
            );

        $precio =
            round(
                (float) $this->precioUnitario,
                2
            );

        $subtotal =
            round(
                $cantidad * $precio,
                2
            );

        /*
        |--------------------------------------------------------------------------
        | Validar monto máximo de la convocatoria
        |--------------------------------------------------------------------------
        */

        $montoMaximo =
            $propuesta
                ->convocatoria
                ?->monto_maximo;

        if ($montoMaximo !== null) {
            $totalActual =
                PresupuestoItem::query()
                    ->where(
                        'propuesta_id',
                        $propuesta->id
                    )
                    ->when(
                        $this->itemEditandoId,
                        fn ($query) =>
                            $query->whereKeyNot(
                                $this
                                    ->itemEditandoId
                            )
                    )
                    ->get()
                    ->sum(
                        fn (
                            PresupuestoItem $item
                        ): float =>
                            (float)
                            $item->cantidad
                            *
                            (float)
                            $item->precio_unitario
                    );

            $nuevoTotal =
                round(
                    $totalActual
                    + $subtotal,
                    2
                );

            if (
                $nuevoTotal >
                (float) $montoMaximo
            ) {
                $this->addError(
                    'precioUnitario',
                    'El presupuesto excedería el monto máximo permitido por la convocatoria.'
                );

                Notification::make()
                    ->title(
                        'Monto máximo excedido'
                    )
                    ->body(
                        'Reduce la cantidad o el precio unitario antes de guardar.'
                    )
                    ->warning()
                    ->send();

                return false;
            }
        }

        if ($this->itemEditandoId) {
            $item =
                $this->itemDelUsuario(
                    $this->itemEditandoId
                );
        } else {
            $item =
                new PresupuestoItem();

            $item->propuesta_id =
                $propuesta->id;
        }

        $item->concepto =
            trim(
                $this->concepto
            );

        $item->descripcion =
            $this->textoONull(
                $this->descripcion
            );

        $item->categoria_gasto =
            $this->categoriaGasto;

        $item->cantidad =
            $cantidad;

        $item->precio_unitario =
            $precio;

        /*
        | El presupuesto trabaja con la moneda
        | de la convocatoria.
        */
        $item->moneda =
            $this->moneda;

        $item->save();

        $eraEdicion =
            $this->itemEditandoId !== null;

        $this->marcarEdicion(
            $propuesta
        );

        $this->limpiarFormulario();

        if ($notificar) {
            Notification::make()
                ->title(
                    $eraEdicion
                        ? 'Concepto actualizado'
                        : 'Concepto agregado'
                )
                ->success()
                ->send();
        }

        return true;
    }

    public function editarConcepto(
        int $itemId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $item =
            $this->itemDelUsuario(
                $itemId
            );

        $this->resetErrorBag();

        $this->itemEditandoId =
            $item->id;

        $this->concepto =
            (string)
            $item->concepto;

        $this->descripcion =
            (string) (
                $item->descripcion
                ?? ''
            );

        $this->categoriaGasto =
            (string) (
                $item->categoria_gasto
                ?: 'OTRO'
            );

        $this->cantidad =
            $this->numeroFormulario(
                $item->cantidad
            );

        $this->precioUnitario =
            $this->numeroFormulario(
                $item->precio_unitario
            );

        $this->moneda =
            (string) (
                $item->moneda
                ?: $this->moneda
            );
    }

    public function cancelarEdicion(): void
    {
        $this->resetErrorBag();

        $this->limpiarFormulario();
    }

    public function eliminarConcepto(
        int $itemId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $item =
            $this->itemDelUsuario(
                $itemId
            );

        /*
        |--------------------------------------------------------------------------
        | No dejar cotizaciones huérfanas
        |--------------------------------------------------------------------------
        */

        if (
            $item->cotizaciones()
                ->exists()
        ) {
            Notification::make()
                ->title(
                    'No se puede eliminar'
                )
                ->body(
                    'Este concepto tiene cotizaciones asociadas. Elimina primero esas cotizaciones.'
                )
                ->warning()
                ->send();

            return;
        }

        $item->delete();

        if (
            $this->itemEditandoId
            === $itemId
        ) {
            $this->limpiarFormulario();
        }

        $this->marcarEdicion();

        Notification::make()
            ->title(
                'Concepto eliminado'
            )
            ->success()
            ->send();
    }

    public function guardarBorrador(): void
    {
        if (
            $this->hayDatosFormulario()
        ) {
            if (
                ! $this->guardarConcepto(
                    false
                )
            ) {
                return;
            }
        } else {
            $propuesta =
                $this
                    ->propuestaDelUsuario();

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
                'Presupuesto guardado'
            )
            ->body(
                'Los conceptos presupuestales están actualizados.'
            )
            ->success()
            ->send();
    }

    public function irACotizaciones(
        ?int $itemId = null
    ): mixed {
        $this->propuestaDelUsuario();

        if ($itemId !== null) {
            $this->itemDelUsuario(
                $itemId
            );
        }

        $parametros = [
            'propuesta' =>
                $this->propuestaId,
        ];

        if ($itemId !== null) {
            $parametros['item'] =
                $itemId;
        }

        return redirect()->route(
            'filament.docente.pages.cotizaciones-propuesta',
            $parametros
        );
    }

    public function continuarACotizaciones(): mixed
    {
        $this->propuestaDelUsuario();

        $tieneConceptos =
            PresupuestoItem::query()
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->exists();

        if (! $tieneConceptos) {
            Notification::make()
                ->title(
                    'Presupuesto incompleto'
                )
                ->body(
                    'Agrega al menos un concepto antes de continuar.'
                )
                ->warning()
                ->send();

            return null;
        }

        return $this->irACotizaciones();
    }

    public function volverARequisitos(): mixed
    {
        $this->propuestaDelUsuario();

        return redirect()->route(
            'filament.docente.pages.requisitos-propuesta',
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

                'requisitos',

                'requisitos as requisitos_cumplidos_count' =>
                    fn ($query) =>
                        $query->where(
                            'cumplido',
                            true
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

        $items =
            PresupuestoItem::query()
                ->withCount(
                    'cotizaciones'
                )
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('id')
                ->get();

        $total =
            round(
                $items->sum(
                    fn (
                        PresupuestoItem $item
                    ): float =>
                        (float)
                        $item->cantidad
                        *
                        (float)
                        $item->precio_unitario
                ),
                2
            );

        $totalesCategoria =
            $items
                ->groupBy(
                    fn (
                        PresupuestoItem $item
                    ): string =>
                        $item->categoria_gasto
                        ?: 'OTRO'
                )
                ->map(
                    fn ($grupo): float =>
                        round(
                            $grupo->sum(
                                fn (
                                    PresupuestoItem $item
                                ): float =>
                                    (float)
                                    $item->cantidad
                                    *
                                    (float)
                                    $item->precio_unitario
                            ),
                            2
                        )
                );

        $montoMaximo =
            $propuesta
                ->convocatoria
                ?->monto_maximo;

        $disponible =
            $montoMaximo !== null
                ? round(
                    (float) $montoMaximo
                    - $total,
                    2
                )
                : null;

        $porcentajePresupuesto =
            $montoMaximo !== null
            && (float) $montoMaximo > 0
                ? min(
                    100,
                    (int) round(
                        (
                            $total
                            / (float) $montoMaximo
                        ) * 100
                    )
                )
                : null;

        $objetivosCompletos =
            $propuesta
                ->objetivos_generales_count > 0
            && $propuesta
                ->objetivos_especificos_count > 0;

        $requisitosCompletos =
            $propuesta
                ->requisitos_count === 0
            || $propuesta
                ->requisitos_cumplidos_count
                ===
                $propuesta
                    ->requisitos_count;

        $presupuestoCompleto =
            $items->isNotEmpty();

        $cotizacionesCompletas =
            $propuesta
                ->cotizaciones_count > 0;

        $cronogramaCompleto =
            $propuesta
                ->actividades_cronograma_count > 0;

        $entregablesCompletos =
            $propuesta
                ->entregables_count > 0;

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

            'convocatoria' =>
                $propuesta->convocatoria,

            'items' =>
                $items,

            'categorias' =>
                self::CATEGORIAS,

            'totalesCategoria' =>
                $totalesCategoria,

            'total' =>
                $total,

            'montoMaximo' =>
                $montoMaximo !== null
                    ? (float)
                        $montoMaximo
                    : null,

            'disponible' =>
                $disponible,

            'porcentajePresupuesto' =>
                $porcentajePresupuesto,

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
            ->with('convocatoria')
            ->whereKey(
                $this->propuestaId
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();
    }

    private function itemDelUsuario(
        int $itemId
    ): PresupuestoItem {
        $this->propuestaDelUsuario();

        return PresupuestoItem::query()
            ->whereKey(
                $itemId
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
                    'Edición de presupuesto';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }
    }

    private function limpiarFormulario(): void
    {
        $this->itemEditandoId = null;
        $this->concepto = '';
        $this->descripcion = '';
        $this->categoriaGasto = 'EQUIPO';
        $this->cantidad = '1';
        $this->precioUnitario = '';
    }

    private function hayDatosFormulario(): bool
    {
        return $this->itemEditandoId !== null
            || trim(
                $this->concepto
            ) !== ''
            || trim(
                $this->descripcion
            ) !== ''
            || trim(
                $this->precioUnitario
            ) !== '';
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
