<?php

namespace App\Filament\Docente\Pages;

use App\Models\Cotizacion;
use App\Models\PresupuestoItem;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class CotizacionesPropuesta extends Page
{
    use WithFileUploads;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Cotizaciones de la Propuesta';

    protected static ?string $slug =
        'cotizaciones-propuesta';

    protected string $view =
        'filament.docente.pages.cotizaciones-propuesta';

    public int $propuestaId;

    public ?int $cotizacionEditandoId = null;

    public string $presupuestoItemId = '';

    public string $proveedor = '';

    public string $concepto = '';

    public string $monto = '';

    public string $moneda = 'MXN';

    public string $fechaCotizacion = '';

    public $archivoCotizacion = null;

    public string $archivoActual = '';

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

        /*
        |--------------------------------------------------------------------------
        | Preselección desde Presupuesto
        |--------------------------------------------------------------------------
        */

        $itemId = request()->integer(
            'item'
        );

        if ($itemId > 0) {
            $item =
                $this->itemDelUsuario(
                    $itemId
                );

            $this->presupuestoItemId =
                (string) $item->id;

            $this->concepto =
                $item->concepto;

            $this->moneda =
                $item->moneda ?: $this->moneda;
        }
    }

    public function updatedPresupuestoItemId(
        mixed $valor
    ): void {
        if (
            blank($valor)
            || ! ctype_digit(
                (string) $valor
            )
        ) {
            return;
        }

        $item =
            PresupuestoItem::query()
                ->whereKey(
                    (int) $valor
                )
                ->where(
                    'propuesta_id',
                    $this->propuestaId
                )
                ->first();

        if (! $item) {
            $this->presupuestoItemId = '';

            return;
        }

        $this->concepto =
            $item->concepto;

        $this->moneda =
            $item->moneda ?: 'MXN';
    }

    public function guardarCotizacion(
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
            'presupuestoItemId' => [
                'required',
                'integer',
            ],

            'proveedor' => [
                'required',
                'string',
                'max:255',
            ],

            'concepto' => [
                'required',
                'string',
                'max:255',
            ],

            'monto' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999999.99',
                'decimal:0,2',
            ],

            'moneda' => [
                'required',
                Rule::in([
                    'MXN',
                    'USD',
                ]),
            ],

            'fechaCotizacion' => [
                'required',
                'date',
            ],

            'archivoCotizacion' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png',
            ],
        ]);

        $item =
            $this->itemDelUsuario(
                (int)
                $this->presupuestoItemId
            );

        /*
        |--------------------------------------------------------------------------
        | Toda cotización nueva debe tener documento.
        |--------------------------------------------------------------------------
        |
        | Al editar se conserva el archivo anterior si no se sube otro.
        |
        */

        if (
            ! $this->archivoCotizacion
            && blank(
                $this->archivoActual
            )
        ) {
            $this->addError(
                'archivoCotizacion',
                'Adjunta el documento de la cotización.'
            );

            return false;
        }

        $rutaNueva = null;
        $rutaAnterior = null;

        try {
            if (
                $this->archivoCotizacion
            ) {
                $rutaNueva =
                    $this
                        ->archivoCotizacion
                        ->store(
                            'cotizaciones/propuestas/'
                            . $this->propuestaId,
                            'local'
                        );
            }

            if (
                $this->cotizacionEditandoId
            ) {
                $cotizacion =
                    $this->cotizacionDelUsuario(
                        $this
                            ->cotizacionEditandoId
                    );

                $rutaAnterior =
                    $cotizacion->archivo_url;
            } else {
                $cotizacion =
                    new Cotizacion();

                $cotizacion->propuesta_id =
                    $propuesta->id;
            }

            $cotizacion
                ->presupuesto_item_id =
                    $item->id;

            $cotizacion->proveedor =
                trim(
                    $this->proveedor
                );

            $cotizacion->concepto =
                trim(
                    $this->concepto
                );

            $cotizacion->monto =
                round(
                    (float)
                    $this->monto,
                    2
                );

            $cotizacion->moneda =
                $this->moneda;

            $cotizacion->fecha_cotizacion =
                $this->fechaCotizacion;

            if ($rutaNueva) {
                $cotizacion->archivo_url =
                    $rutaNueva;
            }

            $cotizacion->save();

            /*
            | El archivo anterior se elimina solo
            | después de guardar exitosamente.
            */
            if (
                $rutaNueva
                && $rutaAnterior
                && $rutaAnterior
                    !== $rutaNueva
                && $this->esRutaLocal(
                    $rutaAnterior
                )
            ) {
                Storage::disk('local')
                    ->delete(
                        $rutaAnterior
                    );
            }
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('local')
                    ->delete(
                        $rutaNueva
                    );
            }

            report($e);

            Notification::make()
                ->title(
                    'No se pudo guardar la cotización'
                )
                ->danger()
                ->send();

            return false;
        }

        $eraEdicion =
            $this
                ->cotizacionEditandoId
            !== null;

        $this->marcarEdicion(
            $propuesta
        );

        $this->limpiarFormulario();

        if ($notificar) {
            Notification::make()
                ->title(
                    $eraEdicion
                        ? 'Cotización actualizada'
                        : 'Cotización agregada'
                )
                ->success()
                ->send();
        }

        return true;
    }

    public function editarCotizacion(
        int $cotizacionId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $cotizacion =
            $this->cotizacionDelUsuario(
                $cotizacionId
            );

        $this->resetErrorBag();

        $this->cotizacionEditandoId =
            $cotizacion->id;

        $this->presupuestoItemId =
            $cotizacion
                ->presupuesto_item_id
                ? (string)
                    $cotizacion
                        ->presupuesto_item_id
                : '';

        $this->proveedor =
            (string) (
                $cotizacion->proveedor
                ?? ''
            );

        $this->concepto =
            (string) (
                $cotizacion->concepto
                ?? ''
            );

        $this->monto =
            $this->numeroFormulario(
                $cotizacion->monto
            );

        $this->moneda =
            $cotizacion->moneda
            ?: 'MXN';

        $this->fechaCotizacion =
            $cotizacion
                ->fecha_cotizacion
                ?->format('Y-m-d')
            ?? '';

        $this->archivoActual =
            (string) (
                $cotizacion
                    ->archivo_url
                ?? ''
            );

        $this->archivoCotizacion =
            null;
    }

    public function cancelarEdicion(): void
    {
        $this->resetErrorBag();

        $this->limpiarFormulario();
    }

    public function eliminarCotizacion(
        int $cotizacionId
    ): void {
        if (! $this->editable()) {
            return;
        }

        $cotizacion =
            $this->cotizacionDelUsuario(
                $cotizacionId
            );

        $ruta =
            $cotizacion->archivo_url;

        $cotizacion->delete();

        if (
            $ruta
            && $this->esRutaLocal($ruta)
        ) {
            Storage::disk('local')
                ->delete($ruta);
        }

        if (
            $this->cotizacionEditandoId
            === $cotizacionId
        ) {
            $this->limpiarFormulario();
        }

        $this->marcarEdicion();

        Notification::make()
            ->title(
                'Cotización eliminada'
            )
            ->success()
            ->send();
    }

    public function descargarCotizacion(
        int $cotizacionId
    ): mixed {
        $cotizacion =
            $this->cotizacionDelUsuario(
                $cotizacionId
            );

        $ruta =
            $cotizacion->archivo_url;

        abort_if(
            blank($ruta),
            404
        );

        abort_if(
            ! $this->esRutaLocal(
                $ruta
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

    public function guardarBorrador(): void
    {
        if (
            $this->hayDatosFormulario()
        ) {
            if (
                ! $this->guardarCotizacion(
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
                'Cotizaciones guardadas'
            )
            ->success()
            ->send();
    }

    public function volverAPresupuesto(): mixed
    {
        $this->propuestaDelUsuario();

        return redirect()->route(
            'filament.docente.pages.presupuesto-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function continuarACronograma(): mixed
    {
        $this->propuestaDelUsuario();

        /*
        | Si el formulario contiene información,
        | la guardamos antes de continuar.
        */
        if (
            $this->hayDatosFormulario()
            && $this->editable()
        ) {
            if (
                ! $this->guardarCotizacion(
                    false
                )
            ) {
                return null;
            }
        }

        /*
        | Las cotizaciones pueden no aplicar
        | a todos los conceptos, por lo que no
        | bloqueamos el avance si no existen.
        */
        return redirect()->route(
            'filament.docente.pages.cronograma-propuesta',
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
                ->where(
                    'propuesta_id',
                    $propuesta->id
                )
                ->orderBy('concepto')
                ->get();

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

        $cotizacionesCompletas =
            $cotizaciones
                ->filter(
                    fn (
                        Cotizacion $cotizacion
                    ): bool =>
                        filled(
                            $cotizacion
                                ->proveedor
                        )
                        && (float)
                            $cotizacion
                                ->monto > 0
                        && $cotizacion
                            ->fecha_cotizacion
                        !== null
                        && filled(
                            $cotizacion
                                ->archivo_url
                        )
                );

        $itemsRespaldados =
            $cotizacionesCompletas
                ->pluck(
                    'presupuesto_item_id'
                )
                ->filter()
                ->unique()
                ->count();

        $conceptosPendientes =
            max(
                0,
                $items->count()
                - $itemsRespaldados
            );

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

        $cotizacionesPasoCompleto =
            $cotizaciones
                ->isNotEmpty();

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
            + ($cotizacionesPasoCompleto ? 1 : 0)
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

            'items' =>
                $items,

            'cotizaciones' =>
                $cotizaciones,

            'editable' =>
                $this->esEstadoEditable(
                    $propuesta->estado
                ),

            'cotizacionesCompletasCount' =>
                $cotizacionesCompletas
                    ->count(),

            'conceptosPendientes' =>
                $conceptosPendientes,

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
                    $cotizacionesPasoCompleto,

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
            ->whereKey($itemId)
            ->where(
                'propuesta_id',
                $this->propuestaId
            )
            ->firstOrFail();
    }

    private function cotizacionDelUsuario(
        int $cotizacionId
    ): Cotizacion {
        $this->propuestaDelUsuario();

        return Cotizacion::query()
            ->whereKey(
                $cotizacionId
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
                    'Gestión de cotizaciones';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }
    }

    private function limpiarFormulario(): void
    {
        $this->cotizacionEditandoId =
            null;

        $this->presupuestoItemId = '';
        $this->proveedor = '';
        $this->concepto = '';
        $this->monto = '';
        $this->fechaCotizacion = '';
        $this->archivoCotizacion = null;
        $this->archivoActual = '';

        $propuesta =
            $this->propuestaDelUsuario();

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

    private function hayDatosFormulario(): bool
    {
        return $this
                ->cotizacionEditandoId
            !== null
            || trim(
                $this->presupuestoItemId
            ) !== ''
            || trim(
                $this->proveedor
            ) !== ''
            || trim(
                $this->concepto
            ) !== ''
            || trim(
                $this->monto
            ) !== ''
            || trim(
                $this->fechaCotizacion
            ) !== ''
            || $this->archivoCotizacion
                !== null;
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

    private function esRutaLocal(
        string $ruta
    ): bool {
        return ! str_starts_with(
            strtolower($ruta),
            'http://'
        )
        && ! str_starts_with(
            strtolower($ruta),
            'https://'
        );
    }
}
