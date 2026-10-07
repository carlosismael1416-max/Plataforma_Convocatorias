<?php

namespace App\Filament\Docente\Pages;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\PropuestaBase;
use App\Models\PropuestaRequisito;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class GenerarPropuesta extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel =
        'Generar propuesta';

    protected static ?string $title =
        'Generar propuesta';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view =
        'filament.docente.pages.generar-propuesta';

    public string $convocatoriaId = '';

    public string $tituloPropuesta = '';

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
            'convocatoria'
        );

        if ($id <= 0) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | La convocatoria recibida por URL debe ser realmente visible
        |--------------------------------------------------------------------------
        */

        $convocatoria = Convocatoria::query()
            ->whereKey($id)
            ->whereIn(
                'estado',
                [
                    'PUBLICADA',
                    'ACTIVA',
                ]
            )
            ->first();

        if (! $convocatoria) {
            Notification::make()
                ->title(
                    'Convocatoria no disponible'
                )
                ->body(
                    'La convocatoria debe estar publicada o activa para generar una propuesta.'
                )
                ->warning()
                ->send();

            return;
        }

        $this->convocatoriaId =
            (string) $convocatoria->id;

        $this->tituloPropuesta =
            'Propuesta - '
            . $convocatoria->titulo;
    }

    public function updatedConvocatoriaId(): void
    {
        $this->resetValidation();

        if ($this->convocatoriaId === '') {
            $this->tituloPropuesta = '';

            return;
        }

        $convocatoria = Convocatoria::query()
            ->whereKey(
                (int) $this->convocatoriaId
            )
            ->whereIn(
                'estado',
                [
                    'PUBLICADA',
                    'ACTIVA',
                ]
            )
            ->first();

        if (! $convocatoria) {
            $this->convocatoriaId = '';
            $this->tituloPropuesta = '';

            return;
        }

        $this->tituloPropuesta =
            'Propuesta - '
            . $convocatoria->titulo;
    }

    public function crearOContinuar(): mixed
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario instanceof User
                && (bool) $usuario->estado
                && $usuario->tieneRol('DOCENTE'),
            403
        );

        $this->validate([
            'convocatoriaId' => [
                'required',
            ],

            'tituloPropuesta' => [
                'required',
                'string',
                'max:500',
            ],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Transacción + bloqueo
            |--------------------------------------------------------------------------
            |
            | El lock evita que un doble clic cree dos propuestas para la misma
            | convocatoria mientras la primera petición todavía se procesa.
            |
            */

            $propuesta = DB::transaction(
                function () use (
                    $usuario
                ): Propuesta {
                    $convocatoria =
                        Convocatoria::query()
                            ->whereKey(
                                (int)
                                $this->convocatoriaId
                            )
                            ->whereIn(
                                'estado',
                                [
                                    'PUBLICADA',
                                    'ACTIVA',
                                ]
                            )
                            ->lockForUpdate()
                            ->with([
                                'organismo',
                                'categoria',
                                'requisitos',
                            ])
                            ->firstOrFail();

                    /*
                    |--------------------------------------------------------------------------
                    | Una propuesta del usuario por convocatoria
                    |--------------------------------------------------------------------------
                    */

                    $propuesta =
                        Propuesta::query()
                            ->where(
                                'user_id',
                                $usuario->id
                            )
                            ->where(
                                'convocatoria_id',
                                $convocatoria->id
                            )
                            ->first();

                    if (! $propuesta) {
                        $base =
                            $this->obtenerOCrearBase(
                                $convocatoria
                            );

                        $propuesta =
                            Propuesta::create([
                                'user_id' =>
                                    $usuario->id,

                                'convocatoria_id' =>
                                    $convocatoria->id,

                                'propuesta_base_id' =>
                                    $base->id,

                                'titulo' =>
                                    trim(
                                        $this
                                            ->tituloPropuesta
                                    ),

                                /*
                                | La descripción sirve como
                                | borrador inicial del resumen.
                                | El Docente podrá editarla.
                                */
                                'resumen' =>
                                    $convocatoria
                                        ->descripcion,

                                'justificacion' =>
                                    null,

                                'metodologia' =>
                                    null,

                                'impacto_esperado' =>
                                    null,

                                'estado' =>
                                    'BORRADOR',

                                'version' => 1,

                                'fecha_envio' =>
                                    null,
                            ]);
                    } else {
                        /*
                        |--------------------------------------------------------------------------
                        | Si ya existía, no crear duplicado
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $propuesta
                                ->propuesta_base_id
                            === null
                        ) {
                            $base =
                                $this->obtenerOCrearBase(
                                    $convocatoria
                                );

                            $propuesta
                                ->propuesta_base_id =
                                    $base->id;

                            $propuesta->save();
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Copiar requisitos de la convocatoria
                    |--------------------------------------------------------------------------
                    */

                    $this->sincronizarRequisitos(
                        $propuesta,
                        $convocatoria
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Mis Convocatorias
                    |--------------------------------------------------------------------------
                    */

                    $seguimiento =
                        UsuarioConvocatoria::query()
                            ->firstOrNew([
                                'user_id' =>
                                    $usuario->id,

                                'convocatoria_id' =>
                                    $convocatoria->id,
                            ]);

                    if (! $seguimiento->exists) {
                        $seguimiento->es_favorita =
                            true;

                        $seguimiento->prioridad =
                            'MEDIA';

                        $seguimiento
                            ->estado_seguimiento =
                                'PREPARANDO_PROPUESTA';
                    } elseif (
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

                    $seguimiento->ultima_accion =
                        'Preparando propuesta';

                    $seguimiento
                        ->fecha_ultima_accion =
                            now();

                    $seguimiento->save();

                    return $propuesta;
                }
            );
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title(
                    'No se pudo preparar la propuesta'
                )
                ->body(
                    'Ocurrió un error al crear la propuesta. Inténtalo nuevamente.'
                )
                ->danger()
                ->send();

            return null;
        }

        Notification::make()
            ->title('Propuesta preparada')
            ->body(
                'Puedes continuar con la edición de la propuesta.'
            )
            ->success()
            ->send();

        return redirect()->route(
            'filament.docente.pages.editor-propuesta',
            [
                'propuesta' =>
                    $propuesta->id,
            ]
        );
    }

    protected function getViewData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Solamente convocatorias disponibles para Docente
        |--------------------------------------------------------------------------
        */

        $convocatorias =
            Convocatoria::query()
                ->with([
                    'organismo',
                    'categoria',
                ])
                ->whereIn(
                    'estado',
                    [
                        'PUBLICADA',
                        'ACTIVA',
                    ]
                )
                ->orderBy('titulo')
                ->get();

        $seleccionada = null;

        $propuestaExistente = null;

        if ($this->convocatoriaId !== '') {
            $seleccionada =
                Convocatoria::query()
                    ->with([
                        'organismo',
                        'categoria',
                        'requisitos',
                    ])
                    ->whereKey(
                        (int)
                        $this->convocatoriaId
                    )
                    ->whereIn(
                        'estado',
                        [
                            'PUBLICADA',
                            'ACTIVA',
                        ]
                    )
                    ->first();

            if ($seleccionada) {
                $propuestaExistente =
                    Propuesta::query()
                        ->where(
                            'user_id',
                            auth()->id()
                        )
                        ->where(
                            'convocatoria_id',
                            $seleccionada->id
                        )
                        ->first();
            }
        }

        return [
            'convocatorias' =>
                $convocatorias,

            'seleccionada' =>
                $seleccionada,

            'propuestaExistente' =>
                $propuestaExistente,
        ];
    }

    private function obtenerOCrearBase(
        Convocatoria $convocatoria
    ): PropuestaBase {
        /*
        |--------------------------------------------------------------------------
        | Reutilizar una base ya preparada
        |--------------------------------------------------------------------------
        */

        $base = PropuestaBase::query()
            ->where(
                'convocatoria_id',
                $convocatoria->id
            )
            ->where(
                'estado',
                'GENERADA'
            )
            ->latest('id')
            ->first();

        if ($base) {
            return $base;
        }

        /*
        |--------------------------------------------------------------------------
        | Base automática
        |--------------------------------------------------------------------------
        |
        | En esta etapa NO fingimos una llamada a IA.
        | Se construye una base real usando la información ya normalizada
        | de la convocatoria y sus requisitos.
        |
        */

        $contenido = [
            'titulo_convocatoria' =>
                $convocatoria->titulo,

            'descripcion' =>
                $convocatoria->descripcion,

            'objetivo' =>
                $convocatoria->objetivo,

            'organismo' =>
                $convocatoria
                    ->organismo?->nombre,

            'categoria' =>
                $convocatoria
                    ->categoria?->nombre,

            'fecha_cierre' =>
                $convocatoria
                    ->fecha_cierre
                    ?->format('Y-m-d'),

            'requisitos' =>
                $convocatoria
                    ->requisitos
                    ->map(
                        fn ($requisito): array => [
                            'id' =>
                                $requisito->id,

                            'titulo' =>
                                $requisito->titulo,

                            'descripcion' =>
                                $requisito
                                    ->descripcion,

                            'obligatorio' =>
                                (bool)
                                $requisito
                                    ->obligatorio,

                            'tipo' =>
                                $requisito
                                    ->tipo_requisito,
                        ]
                    )
                    ->values()
                    ->all(),
        ];

        return PropuestaBase::create([
            'convocatoria_id' =>
                $convocatoria->id,

            'contenido_generado' =>
                $contenido,

            /*
            | Todavía no existe integración real
            | con un modelo de IA.
            */
            'modelo_ia' => null,

            'prompt_utilizado' => null,

            'version' => 1,

            'estado' => 'GENERADA',

            'mensaje_error' => null,

            'fecha_generacion' => now(),
        ]);
    }

    private function sincronizarRequisitos(
        Propuesta $propuesta,
        Convocatoria $convocatoria
    ): void {
        foreach (
            $convocatoria->requisitos
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
}
