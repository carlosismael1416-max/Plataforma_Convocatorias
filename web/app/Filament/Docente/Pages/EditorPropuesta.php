<?php

namespace App\Filament\Docente\Pages;

use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EditorPropuesta extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title =
        'Editor de propuesta';

    protected string $view =
        'filament.docente.pages.editor-propuesta';

    public int $propuestaId;

    public string $titulo = '';

    public string $resumen = '';

    public string $justificacion = '';

    public string $metodologia = '';

    public string $impactoEsperado = '';

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

        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | Nunca se carga una propuesta solamente por ID.
        | Debe pertenecer al Docente autenticado.
        |
        */

        $propuesta = Propuesta::query()
            ->whereKey($id)
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();

        $this->propuestaId =
            $propuesta->id;

        $this->titulo =
            (string) $propuesta->titulo;

        $this->resumen =
            (string) ($propuesta->resumen ?? '');

        $this->justificacion =
            (string) (
                $propuesta->justificacion
                ?? ''
            );

        $this->metodologia =
            (string) (
                $propuesta->metodologia
                ?? ''
            );

        $this->impactoEsperado =
            (string) (
                $propuesta->impacto_esperado
                ?? ''
            );
    }

    public function guardarBorrador(): void
    {
        $propuesta =
            $this->guardarCambios();

        if (! $propuesta) {
            return;
        }

        Notification::make()
            ->title('Borrador guardado')
            ->body(
                'Los cambios de la propuesta se guardaron correctamente.'
            )
            ->success()
            ->send();
    }

    public function continuarAObjetivos(): mixed
    {
        $propuesta =
            $this->propuestaDelUsuario();

        if (
            $this->esEditable(
                $propuesta->estado
            )
        ) {
            $propuesta =
                $this->guardarCambios();

            if (! $propuesta) {
                return null;
            }
        }

        return redirect()->route(
            'filament.docente.pages.objetivos-propuesta',
            [
                'propuesta' =>
                    $this->propuestaId,
            ]
        );
    }

    public function irAVistaPrevia(): mixed
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
                'usuario',
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

        $requisitosCumplidos =
            $propuesta->requisitos()
                ->where(
                    'cumplido',
                    true
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Progreso real
        |--------------------------------------------------------------------------
        |
        | Son siete secciones:
        |
        | 1. Datos
        | 2. Objetivos
        | 3. Requisitos
        | 4. Presupuesto
        | 5. Cotizaciones
        | 6. Cronograma
        | 7. Entregables
        |
        | Datos admite progreso parcial según los cinco campos principales.
        |
        */

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

        $objetivosCompletos =
            $propuesta
                ->objetivos_generales_count > 0
            && $propuesta
                ->objetivos_especificos_count > 0;

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

        $progreso = (int) round(
            ($puntaje / 7) * 100
        );

        $convocatoria =
            $propuesta->convocatoria;

        $montoMaximo =
            $convocatoria?->monto_maximo !== null
                ? '$'
                    . number_format(
                        (float)
                        $convocatoria->monto_maximo,
                        2
                    )
                    . ' '
                    . (
                        $convocatoria->moneda
                        ?? ''
                    )
                : 'No especificado';

        $editable =
            $this->esEditable(
                $propuesta->estado
            );

        return [
            'propuesta' =>
                $propuesta,

            'convocatoria' =>
                $convocatoria,

            'editable' =>
                $editable,

            'progreso' =>
                min(100, $progreso),

            'montoMaximo' =>
                trim($montoMaximo),

            'requisitosCumplidos' =>
                $requisitosCumplidos,

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

    private function guardarCambios(): ?Propuesta
    {
        $propuesta =
            $this->propuestaDelUsuario();

        if (
            ! $this->esEditable(
                $propuesta->estado
            )
        ) {
            Notification::make()
                ->title(
                    'Propuesta de solo lectura'
                )
                ->body(
                    'Esta propuesta ya no puede modificarse debido a su estado actual.'
                )
                ->warning()
                ->send();

            return null;
        }

        $this->validate([
            'titulo' => [
                'required',
                'string',
                'max:500',
            ],

            'resumen' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'justificacion' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'metodologia' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'impactoEsperado' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ]);

        $propuesta->titulo =
            trim($this->titulo);

        $propuesta->resumen =
            $this->textoONull(
                $this->resumen
            );

        $propuesta->justificacion =
            $this->textoONull(
                $this->justificacion
            );

        $propuesta->metodologia =
            $this->textoONull(
                $this->metodologia
            );

        $propuesta->impacto_esperado =
            $this->textoONull(
                $this->impactoEsperado
            );

        /*
        |--------------------------------------------------------------------------
        | Una propuesta modificada entra en edición
        |--------------------------------------------------------------------------
        */

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
        }

        $propuesta->save();

        /*
        |--------------------------------------------------------------------------
        | Actualizar seguimiento de Mis Convocatorias
        |--------------------------------------------------------------------------
        */

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
                    'Edición de propuesta';

            $seguimiento
                ->fecha_ultima_accion =
                    now();

            $seguimiento->save();
        }

        return $propuesta;
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

    private function esEditable(
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

    private function textoONull(
        string $texto
    ): ?string {
        $texto = trim($texto);

        return $texto !== ''
            ? $texto
            : null;
    }
}
