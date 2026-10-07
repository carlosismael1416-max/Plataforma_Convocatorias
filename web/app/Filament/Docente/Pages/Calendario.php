<?php

namespace App\Filament\Docente\Pages;

use App\Models\Convocatoria;
use App\Models\EventoCalendario;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Calendario extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel =
        'Calendario';

    protected static ?string $title =
        'Calendario';

    protected static ?int $navigationSort = 3;

    protected string $view =
        'filament.docente.pages.calendario';

    public int $mes;

    public int $anio;

    public bool $mostrarNuevoEvento = false;

    public ?int $eventoEditandoId = null;

    public string $tituloEvento = '';

    public string $descripcionEvento = '';

    public string $fechaEvento = '';

    public string $horaEvento = '09:00';

    public string $tipoEvento = 'RECORDATORIO';

    public bool $recordatorioEvento = false;

    public string $minutosRecordatorio = '';

    public string $convocatoriaEvento = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function mount(): void
    {
        $hoy = now();

        $this->mes = (int) $hoy->month;
        $this->anio = (int) $hoy->year;
    }

    public function mesAnterior(): void
    {
        $fecha = CarbonImmutable::create(
            $this->anio,
            $this->mes,
            1
        )->subMonth();

        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
    }

    public function mesSiguiente(): void
    {
        $fecha = CarbonImmutable::create(
            $this->anio,
            $this->mes,
            1
        )->addMonth();

        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
    }

    public function irHoy(): void
    {
        $hoy = now();

        $this->mes = (int) $hoy->month;
        $this->anio = (int) $hoy->year;
    }

    public function abrirNuevoEvento(
        ?string $fecha = null
    ): void {
        $this->resetFormularioEvento();

        if ($fecha) {
            try {
                $this->fechaEvento = Carbon::parse(
                    $fecha
                )->format('Y-m-d');
            } catch (\Throwable) {
                $this->fechaEvento = now()
                    ->format('Y-m-d');
            }
        } else {
            $mesMostrado = CarbonImmutable::create(
                $this->anio,
                $this->mes,
                1
            );

            $this->fechaEvento =
                $mesMostrado->isSameMonth(now())
                    ? now()->format('Y-m-d')
                    : $mesMostrado->format('Y-m-d');
        }

        $this->mostrarNuevoEvento = true;
    }

    public function editarEvento(
        int $eventoId
    ): void {
        $evento = $this->eventoDelUsuario(
            $eventoId
        );

        $this->eventoEditandoId = $evento->id;

        $this->tituloEvento = $evento->titulo;

        $this->descripcionEvento =
            (string) ($evento->descripcion ?? '');

        $this->fechaEvento =
            $evento->fecha_inicio->format('Y-m-d');

        $this->horaEvento =
            $evento->fecha_inicio->format('H:i');

        $this->tipoEvento =
            $evento->tipo_evento;

        $this->recordatorioEvento =
            (bool) $evento->recordatorio;

        $this->minutosRecordatorio =
            $evento->minutos_recordatorio !== null
                ? (string) $evento->minutos_recordatorio
                : '';

        $this->convocatoriaEvento =
            $evento->convocatoria_id !== null
                ? (string) $evento->convocatoria_id
                : '';

        $this->resetValidation();

        $this->mostrarNuevoEvento = true;
    }

    public function cerrarNuevoEvento(): void
    {
        $this->mostrarNuevoEvento = false;

        $this->resetFormularioEvento();
    }

    public function guardarEvento(): void
    {
        $usuario = auth()->user();

        abort_unless(
            $usuario instanceof User
                && (bool) $usuario->estado
                && $usuario->tieneRol('DOCENTE'),
            403
        );

        $this->validate([
            'tituloEvento' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcionEvento' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'fechaEvento' => [
                'required',
                'date',
            ],

            'horaEvento' => [
                'required',
                'date_format:H:i',
            ],

            'tipoEvento' => [
                'required',
                'in:REUNION,RECORDATORIO,ENTREGABLE,OTRO',
            ],

            'recordatorioEvento' => [
                'boolean',
            ],

            'minutosRecordatorio' => [
                'nullable',
                'integer',
                'min:0',
                'max:525600',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | La convocatoria asociada debe ser visible para Docente
        |--------------------------------------------------------------------------
        */

        $convocatoriaId = null;

        if ($this->convocatoriaEvento !== '') {
            $convocatoria = Convocatoria::query()
                ->whereKey(
                    (int) $this->convocatoriaEvento
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
                $this->addError(
                    'convocatoriaEvento',
                    'La convocatoria seleccionada ya no está disponible.'
                );

                return;
            }

            $convocatoriaId =
                $convocatoria->id;
        }

        $fechaInicio = Carbon::createFromFormat(
            'Y-m-d H:i',
            $this->fechaEvento
                . ' '
                . $this->horaEvento,
            config('app.timezone')
        );

        if ($this->eventoEditandoId) {
            $evento = $this->eventoDelUsuario(
                $this->eventoEditandoId
            );
        } else {
            /*
            |--------------------------------------------------------------------------
            | Evitar eventos duplicados por múltiples clics
            |--------------------------------------------------------------------------
            */

            $eventoExistente = EventoCalendario::query()
                ->where('user_id', $usuario->id)
                ->where(
                    'titulo',
                    trim($this->tituloEvento)
                )
                ->where(
                    'fecha_inicio',
                    $fechaInicio
                )
                ->where(
                    'tipo_evento',
                    $this->tipoEvento
                )
                ->first();

            if ($eventoExistente) {
                $this->mostrarNuevoEvento = false;

                $this->resetFormularioEvento();

                Notification::make()
                    ->title('Evento ya registrado')
                    ->body(
                        'Este evento ya había sido creado.'
                    )
                    ->warning()
                    ->send();

                return;
            }

            $evento = new EventoCalendario();

            $evento->user_id = $usuario->id;
        }

        $evento->convocatoria_id =
            $convocatoriaId;

        $evento->titulo =
            trim($this->tituloEvento);

        $descripcion =
            trim($this->descripcionEvento);

        $evento->descripcion =
            $descripcion !== ''
                ? $descripcion
                : null;

        $evento->fecha_inicio =
            $fechaInicio;

        $evento->fecha_fin = null;

        $evento->tipo_evento =
            $this->tipoEvento;

        $evento->recordatorio =
            $this->recordatorioEvento;

        $evento->minutos_recordatorio =
            $this->recordatorioEvento
            && $this->minutosRecordatorio !== ''
                ? (int) $this->minutosRecordatorio
                : null;

        $evento->estado = true;

        $evento->save();

        Notification::make()
            ->title(
                $this->eventoEditandoId
                    ? 'Evento actualizado'
                    : 'Evento creado'
            )
            ->success()
            ->send();

        $this->mostrarNuevoEvento = false;

        $this->resetFormularioEvento();
    }

    public function eliminarEvento(
        int $eventoId
    ): void {
        $evento = $this->eventoDelUsuario(
            $eventoId
        );

        $evento->delete();

        if (
            $this->eventoEditandoId
            === $eventoId
        ) {
            $this->mostrarNuevoEvento = false;

            $this->resetFormularioEvento();
        }

        Notification::make()
            ->title('Evento eliminado')
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $usuarioId = auth()->id();

        $primerDia = CarbonImmutable::create(
            $this->anio,
            $this->mes,
            1
        );

        $inicioCalendario =
            $primerDia->startOfWeek(
                CarbonInterface::MONDAY
            );

        $finCalendario =
            $inicioCalendario->addDays(41);

        $dias = collect(range(0, 41))
            ->map(
                fn (int $dia) =>
                    $inicioCalendario
                        ->addDays($dia)
            );

        /*
        |--------------------------------------------------------------------------
        | Convocatorias guardadas por el usuario
        |--------------------------------------------------------------------------
        */

        $guardadasIds =
            UsuarioConvocatoria::query()
                ->where(
                    'user_id',
                    $usuarioId
                )
                ->pluck(
                    'convocatoria_id'
                )
                ->map(
                    fn ($id): int =>
                        (int) $id
                )
                ->all();

        /*
        |--------------------------------------------------------------------------
        | Cierres automáticos
        |--------------------------------------------------------------------------
        */

        $convocatorias = Convocatoria::query()
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
            ->whereNotNull('fecha_cierre')
            ->whereDate(
                'fecha_cierre',
                '>=',
                $inicioCalendario
                    ->toDateString()
            )
            ->whereDate(
                'fecha_cierre',
                '<=',
                $finCalendario
                    ->toDateString()
            )
            ->orderBy('fecha_cierre')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Eventos personales
        |--------------------------------------------------------------------------
        */

        $eventosUsuario =
            EventoCalendario::query()
                ->with('convocatoria')
                ->where(
                    'user_id',
                    $usuarioId
                )
                ->where('estado', true)
                ->whereBetween(
                    'fecha_inicio',
                    [
                        $inicioCalendario
                            ->startOfDay(),
                        $finCalendario
                            ->endOfDay(),
                    ]
                )
                ->orderBy('fecha_inicio')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Unificar los eventos del calendario
        |--------------------------------------------------------------------------
        */

        $eventos = collect();

        foreach (
            $convocatorias
            as $convocatoria
        ) {
            $eventos->push([
                'tipo' => 'CIERRE',
                'id' => $convocatoria->id,
                'titulo' => $convocatoria->titulo,
                'fecha' => $convocatoria
                    ->fecha_cierre
                    ->format('Y-m-d'),
                'fecha_orden' => $convocatoria
                    ->fecha_cierre
                    ->startOfDay()
                    ->timestamp,
                'hora' => null,
                'convocatoria_id' =>
                    $convocatoria->id,
                'guardada' => in_array(
                    (int) $convocatoria->id,
                    $guardadasIds,
                    true
                ),
                'organismo' =>
                    $convocatoria
                        ->organismo?->nombre,
            ]);
        }

        foreach (
            $eventosUsuario
            as $evento
        ) {
            $eventos->push([
                'tipo' => $evento->tipo_evento,
                'id' => $evento->id,
                'titulo' => $evento->titulo,
                'fecha' => $evento
                    ->fecha_inicio
                    ->format('Y-m-d'),
                'fecha_orden' => $evento
                    ->fecha_inicio
                    ->timestamp,
                'hora' => $evento
                    ->fecha_inicio
                    ->format('H:i'),
                'convocatoria_id' =>
                    $evento->convocatoria_id,
                'guardada' => false,
                'organismo' => null,
            ]);
        }

        $eventosPorDia = $eventos
            ->sortBy('fecha_orden')
            ->groupBy('fecha');

        /*
        |--------------------------------------------------------------------------
        | Próximos cierres
        |--------------------------------------------------------------------------
        */

        $proximosCierres =
            Convocatoria::query()
                ->with('organismo')
                ->whereIn(
                    'estado',
                    [
                        'PUBLICADA',
                        'ACTIVA',
                    ]
                )
                ->whereNotNull(
                    'fecha_cierre'
                )
                ->whereDate(
                    'fecha_cierre',
                    '>=',
                    today()
                )
                ->orderBy(
                    'fecha_cierre'
                )
                ->limit(20)
                ->get()
                ->map(
                    function (
                        Convocatoria $convocatoria
                    ) use ($guardadasIds): array {
                        return [
                            'tipo' => 'CIERRE',
                            'id' => $convocatoria->id,
                            'titulo' =>
                                $convocatoria->titulo,
                            'fecha_orden' =>
                                $convocatoria
                                    ->fecha_cierre
                                    ->startOfDay()
                                    ->timestamp,
                            'fecha_texto' =>
                                $convocatoria
                                    ->fecha_cierre
                                    ->format('d/m/Y'),
                            'hora' => null,
                            'convocatoria_id' =>
                                $convocatoria->id,
                            'guardada' =>
                                in_array(
                                    (int)
                                    $convocatoria->id,
                                    $guardadasIds,
                                    true
                                ),
                        ];
                    }
                );

        /*
        |--------------------------------------------------------------------------
        | Próximos eventos personales
        |--------------------------------------------------------------------------
        */

        $proximosPersonales =
            EventoCalendario::query()
                ->where(
                    'user_id',
                    $usuarioId
                )
                ->where('estado', true)
                ->where(
                    'fecha_inicio',
                    '>=',
                    now()
                )
                ->orderBy(
                    'fecha_inicio'
                )
                ->limit(20)
                ->get()
                ->map(
                    fn (
                        EventoCalendario $evento
                    ): array => [
                        'tipo' =>
                            $evento->tipo_evento,
                        'id' => $evento->id,
                        'titulo' =>
                            $evento->titulo,
                        'fecha_orden' =>
                            $evento
                                ->fecha_inicio
                                ->timestamp,
                        'fecha_texto' =>
                            $evento
                                ->fecha_inicio
                                ->format('d/m/Y'),
                        'hora' =>
                            $evento
                                ->fecha_inicio
                                ->format('H:i'),
                        'convocatoria_id' =>
                            $evento
                                ->convocatoria_id,
                        'guardada' => false,
                    ]
                );

        $proximosEventos =
            $proximosCierres
                ->concat(
                    $proximosPersonales
                )
                ->sortBy('fecha_orden')
                ->take(8)
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Convocatorias para asociar a eventos personales
        |--------------------------------------------------------------------------
        */

        $convocatoriasSeleccionables =
            Convocatoria::query()
                ->whereIn(
                    'estado',
                    [
                        'PUBLICADA',
                        'ACTIVA',
                    ]
                )
                ->orderBy('titulo')
                ->get([
                    'id',
                    'titulo',
                ]);

        $inicioMes =
            $primerDia->startOfMonth();

        $finMes =
            $primerDia->endOfMonth();

        $cierresMes =
            Convocatoria::query()
                ->whereIn(
                    'estado',
                    [
                        'PUBLICADA',
                        'ACTIVA',
                    ]
                )
                ->whereNotNull(
                    'fecha_cierre'
                )
                ->whereBetween(
                    'fecha_cierre',
                    [
                        $inicioMes
                            ->toDateString(),
                        $finMes
                            ->toDateString(),
                    ]
                )
                ->count();

        $eventosPersonalesMes =
            EventoCalendario::query()
                ->where(
                    'user_id',
                    $usuarioId
                )
                ->where('estado', true)
                ->whereBetween(
                    'fecha_inicio',
                    [
                        $inicioMes
                            ->startOfDay(),
                        $finMes
                            ->endOfDay(),
                    ]
                )
                ->count();

        return [
            'dias' => $dias,

            'eventosPorDia' =>
                $eventosPorDia,

            'proximosEventos' =>
                $proximosEventos,

            'convocatoriasSeleccionables' =>
                $convocatoriasSeleccionables,

            'nombreMes' => $primerDia
                ->locale('es')
                ->translatedFormat('F Y'),

            'cierresMes' =>
                $cierresMes,

            'eventosPersonalesMes' =>
                $eventosPersonalesMes,
        ];
    }

    private function eventoDelUsuario(
        int $eventoId
    ): EventoCalendario {
        return EventoCalendario::query()
            ->whereKey($eventoId)
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();
    }

    private function resetFormularioEvento(): void
    {
        $this->eventoEditandoId = null;

        $this->tituloEvento = '';

        $this->descripcionEvento = '';

        $this->fechaEvento = '';

        $this->horaEvento = '09:00';

        $this->tipoEvento = 'RECORDATORIO';

        $this->recordatorioEvento = false;

        $this->minutosRecordatorio = '';

        $this->convocatoriaEvento = '';

        $this->resetValidation();
    }
}
