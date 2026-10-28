<?php

namespace App\Filament\Docente\Pages;

use App\Filament\Concerns\RolDelPanel;
use App\Models\Evidencia;
use App\Models\Propuesta;
use App\Models\ProyectoInfraestructura;
use App\Models\User;
use App\Services\RevisionPropuesta;
use App\Support\TextoEnriquecido;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

/**
 * Propuesta de infraestructura (modelo del Fondo de Aportaciones Múltiples,
 * Guía DGESUI 2027): diagnóstico de la capacidad física instalada (máx. 15
 * cuartillas), hasta 5 proyectos priorizados y sus láminas de
 * presentación. El monto FAM de cada proyecto sale del presupuesto
 * (partidas del clasificador) asignado a ese proyecto.
 */
class InfraestructuraPropuesta extends Page
{
    use RolDelPanel;
    use WithFileUploads;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Infraestructura de la propuesta';

    protected string $view = 'filament.docente.pages.infraestructura-propuesta';

    protected ?string $heading = '';

    /** Unas 15 cuartillas. */
    public const LIMITE_DIAGNOSTICO = 45000;

    #[Locked]
    public int $propuestaId;

    public string $diagnostico = '';

    /** Proyecto que se edita (null = ninguno; 0 = nuevo). */
    public ?int $proyectoEditando = null;

    public array $proyecto = [];

    /** Proyecto al que se agrega una lámina. */
    public ?int $laminaProyectoId = null;

    public string $laminaTipo = '';

    public string $laminaNombre = '';

    public $laminaArchivo = null;

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(static::rolDelPanel());
    }

    public function mount(): void
    {
        $id = request()->integer('propuesta');
        abort_if($id <= 0, 404, 'Propuesta no especificada.');

        $propuesta = Propuesta::query()->whereKey($id)->where('user_id', auth()->id())->firstOrFail();

        $this->propuestaId = $propuesta->id;
        $this->diagnostico = (string) ($propuesta->diagnostico ?? '');
    }

    public function usarFormatoInfraestructura(): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        $propuesta->tipo = 'INFRAESTRUCTURA';
        $this->marcarEdicion($propuesta);

        Notification::make()
            ->title('Propuesta de infraestructura')
            ->body('Agrega el diagnóstico y hasta 5 proyectos priorizados con sus láminas.')
            ->success()
            ->send();
    }

    public function usarFormatoInvestigacion(): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        // Los proyectos y sus láminas se conservan por si vuelve a cambiar.
        $propuesta->tipo = 'INVESTIGACION';
        $this->marcarEdicion($propuesta);
    }

    public function guardarDiagnostico(): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        $this->validate([
            'diagnostico' => ['nullable', 'string', 'max:400000', new \App\Rules\LongitudTexto(self::LIMITE_DIAGNOSTICO)],
        ]);

        $html = TextoEnriquecido::sanear($this->diagnostico);
        $propuesta->diagnostico = $html !== '' ? $html : null;
        $this->marcarEdicion($propuesta);

        Notification::make()->title('Diagnóstico guardado')->success()->send();
    }

    public function nuevoProyecto(): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        if ($propuesta->proyectosInfraestructura()->count() >= ProyectoInfraestructura::MAXIMO_POR_PROPUESTA) {
            Notification::make()
                ->title('Máximo 5 proyectos')
                ->body('La guía del FAM admite hasta 5 proyectos por propuesta institucional.')
                ->warning()
                ->send();

            return;
        }

        $this->resetValidation();
        $this->proyectoEditando = 0;
        $this->proyecto = ['tipo_proyecto' => 'EQUIPAMIENTO'];
    }

    public function editarProyecto(int $id): void
    {
        $proyecto = $this->proyectoDelUsuario($id);

        $this->resetValidation();
        $this->proyectoEditando = $proyecto->id;
        $this->proyecto = [
            ...$proyecto->only([
                'nombre', 'tipo_proyecto', 'objetivo', 'justificacion', 'impacto', 'descripcion', 'campus',
                'municipio', 'coordenadas', 'alumnos_mujeres', 'alumnos_hombres', 'personal_academico',
                'superficie_m2', 'monto_otros_fondos', 'financiamiento_previo',
            ]),
            'fecha_inicio' => $proyecto->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $proyecto->fecha_fin?->format('Y-m-d'),
        ];
    }

    public function cancelarProyecto(): void
    {
        $this->proyectoEditando = null;
        $this->proyecto = [];
        $this->resetValidation();
    }

    public function guardarProyecto(): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta || $this->proyectoEditando === null) {
            return;
        }

        $texto = ['nullable', 'string', 'max:20000'];
        $entero = ['nullable', 'integer', 'min:0', 'max:10000000'];

        $datos = $this->validate([
            'proyecto.nombre' => ['required', 'string', 'max:300'],
            'proyecto.tipo_proyecto' => ['required', Rule::in(array_keys(ProyectoInfraestructura::TIPOS))],
            'proyecto.objetivo' => $texto,
            'proyecto.justificacion' => $texto,
            'proyecto.impacto' => $texto,
            'proyecto.descripcion' => $texto,
            'proyecto.campus' => ['nullable', 'string', 'max:255'],
            'proyecto.municipio' => ['nullable', 'string', 'max:150'],
            'proyecto.coordenadas' => ['nullable', 'string', 'max:80', 'regex:/^\s*-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?\s*$/'],
            'proyecto.alumnos_mujeres' => $entero,
            'proyecto.alumnos_hombres' => $entero,
            'proyecto.personal_academico' => $entero,
            'proyecto.superficie_m2' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'proyecto.fecha_inicio' => ['nullable', 'date'],
            'proyecto.fecha_fin' => ['nullable', 'date', 'after_or_equal:proyecto.fecha_inicio'],
            'proyecto.monto_otros_fondos' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'proyecto.financiamiento_previo' => $texto,
        ], [
            'proyecto.nombre.required' => 'Escribe el nombre del proyecto.',
            'proyecto.coordenadas.regex' => 'Escribe las coordenadas como «latitud, longitud» (p. ej. 20.6896, -88.2016).',
            'proyecto.fecha_fin.after_or_equal' => 'La fecha de término no puede ser anterior a la de inicio.',
        ])['proyecto'];

        $datos = collect($datos)
            ->map(fn ($valor) => is_string($valor) && trim($valor) === '' ? null : (is_string($valor) ? trim($valor) : $valor))
            ->all();

        DB::transaction(function () use ($propuesta, $datos): void {
            if ($this->proyectoEditando === 0) {
                // Bloquea la propuesta: dos altas a la vez no toman la misma prioridad.
                Propuesta::query()->whereKey($propuesta->id)->lockForUpdate()->first();
                $prioridad = ((int) $propuesta->proyectosInfraestructura()->max('prioridad')) + 1;
                $propuesta->proyectosInfraestructura()->create([...$datos, 'prioridad' => $prioridad]);
            } else {
                $this->proyectoDelUsuario($this->proyectoEditando)->update($datos);
            }
        });

        $this->marcarEdicion($propuesta);
        $this->cancelarProyecto();

        Notification::make()->title('Proyecto guardado')->success()->send();
    }

    public function moverProyecto(int $id, string $direccion): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        $proyecto = $this->proyectoDelUsuario($id);
        $vecino = $propuesta->proyectosInfraestructura()
            ->where('prioridad', $direccion === 'arriba' ? '<' : '>', $proyecto->prioridad)
            ->reorder('prioridad', $direccion === 'arriba' ? 'desc' : 'asc')
            ->first();

        if (! $vecino) {
            return;
        }

        // La prioridad es única por propuesta: se intercambia en tres pasos.
        DB::transaction(function () use ($proyecto, $vecino): void {
            [$a, $b] = [$proyecto->prioridad, $vecino->prioridad];
            $proyecto->update(['prioridad' => 0]);
            $vecino->update(['prioridad' => $a]);
            $proyecto->update(['prioridad' => $b]);
        });

        $this->marcarEdicion($propuesta);
    }

    public function eliminarProyecto(int $id): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        $proyecto = $this->proyectoDelUsuario($id);

        DB::transaction(function () use ($propuesta, $proyecto): void {
            foreach ($proyecto->laminas as $lamina) {
                if (filled($lamina->ruta_archivo)) {
                    Storage::disk('local')->delete($lamina->ruta_archivo);
                }
            }

            $proyecto->delete();

            // Prioridades seguidas (1, 2, 3…).
            foreach ($propuesta->proyectosInfraestructura()->get() as $i => $restante) {
                $restante->update(['prioridad' => $i + 1]);
            }
        });

        $this->marcarEdicion($propuesta);

        Notification::make()
            ->title('Proyecto eliminado')
            ->body('Sus conceptos de presupuesto y actividades del cronograma se conservan sin proyecto asignado.')
            ->success()
            ->send();
    }

    public function abrirLamina(int $proyectoId, string $tipo = ''): void
    {
        $proyecto = $this->proyectoDelUsuario($proyectoId);

        $this->resetValidation();
        $this->laminaProyectoId = $proyecto->id;
        $this->laminaTipo = array_key_exists($tipo, ProyectoInfraestructura::LAMINAS) ? $tipo : '';
        $this->laminaNombre = $this->laminaTipo !== '' ? ProyectoInfraestructura::LAMINAS[$this->laminaTipo] : '';
        $this->laminaArchivo = null;
    }

    public function cerrarLamina(): void
    {
        $this->laminaProyectoId = null;
        $this->laminaTipo = '';
        $this->laminaNombre = '';
        $this->laminaArchivo = null;
        $this->resetValidation();
    }

    public function guardarLamina(): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta || ! $this->laminaProyectoId) {
            return;
        }

        $proyecto = $this->proyectoDelUsuario($this->laminaProyectoId);

        $this->validate([
            'laminaTipo' => ['required', Rule::in(array_keys(ProyectoInfraestructura::LAMINAS))],
            'laminaNombre' => ['required', 'string', 'max:255'],
            'laminaArchivo' => ['required', 'file', 'max:15360', 'mimes:pdf,jpg,jpeg,png,webp'],
        ], [
            'laminaTipo.required' => 'Elige qué lámina es.',
            'laminaArchivo.required' => 'Adjunta la lámina (PDF o imagen).',
            'laminaArchivo.mimes' => 'La lámina debe ser PDF o imagen (JPG, PNG o WebP).',
            'laminaArchivo.max' => 'La lámina no debe pesar más de 15 MB.',
        ]);

        $ruta = $this->laminaArchivo->store('evidencias/propuestas/' . $propuesta->id . '/laminas', 'local');

        try {
            Evidencia::create([
                'propuesta_id' => $propuesta->id,
                'proyecto_infraestructura_id' => $proyecto->id,
                'tipo_lamina' => $this->laminaTipo,
                'nombre' => trim($this->laminaNombre),
                'ruta_archivo' => $ruta,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($ruta);
            report($e);
            Notification::make()->title('No se pudo guardar la lámina')->danger()->send();

            return;
        }

        $this->marcarEdicion($propuesta);
        $this->cerrarLamina();

        Notification::make()->title('Lámina agregada')->success()->send();
    }

    public function eliminarLamina(int $id): void
    {
        $propuesta = $this->propuestaEditable();

        if (! $propuesta) {
            return;
        }

        $lamina = $this->laminaDelUsuario($id);

        if (filled($lamina->ruta_archivo)) {
            Storage::disk('local')->delete($lamina->ruta_archivo);
        }

        $lamina->delete();
        $this->marcarEdicion($propuesta);
    }

    public function descargarLamina(int $id): mixed
    {
        $lamina = $this->laminaDelUsuario($id);

        abort_if(blank($lamina->ruta_archivo) || ! Storage::disk('local')->exists($lamina->ruta_archivo), 404);

        $extension = pathinfo($lamina->ruta_archivo, PATHINFO_EXTENSION);

        return Storage::disk('local')->download(
            $lamina->ruta_archivo,
            (Str::slug($lamina->nombre) ?: 'lamina') . ($extension !== '' ? '.' . $extension : '')
        );
    }

    protected function getViewData(): array
    {
        $propuesta = Propuesta::query()
            ->with([
                'convocatoria',
                'proyectosInfraestructura.itemsPresupuesto',
                'proyectosInfraestructura.laminas',
                'proyectosInfraestructura.actividades',
            ])
            ->whereKey($this->propuestaId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $proyectos = $propuesta->proyectosInfraestructura;

        return [
            'propuesta' => $propuesta,
            'editable' => in_array($propuesta->estado, RevisionPropuesta::EDITABLES, true),
            'proyectos' => $proyectos,
            'tipos' => ProyectoInfraestructura::TIPOS,
            'laminas' => ProyectoInfraestructura::LAMINAS,
            'requeridas' => ProyectoInfraestructura::LAMINAS_REQUERIDAS,
            'maximo' => ProyectoInfraestructura::MAXIMO_POR_PROPUESTA,
            'totalFam' => $proyectos->sum(fn (ProyectoInfraestructura $p) => $p->montoFam()),
            'totalOtros' => $proyectos->sum(fn (ProyectoInfraestructura $p) => (float) $p->monto_otros_fondos),
            'sinProyecto' => $propuesta->itemsPresupuesto()->whereNull('proyecto_infraestructura_id')->count(),
            // Una cuartilla ≈ 3,000 caracteres; se redondea hacia arriba.
            'cuartillas' => ceil(TextoEnriquecido::longitud($propuesta->diagnostico) / 300) / 10,
        ];
    }

    private function propuestaEditable(): ?Propuesta
    {
        $propuesta = Propuesta::query()->whereKey($this->propuestaId)->where('user_id', auth()->id())->firstOrFail();

        if (! in_array($propuesta->estado, RevisionPropuesta::EDITABLES, true)) {
            Notification::make()
                ->title('Propuesta de solo lectura')
                ->body('Esta propuesta ya no puede modificarse debido a su estado actual.')
                ->warning()
                ->send();

            return null;
        }

        return $propuesta;
    }

    private function proyectoDelUsuario(int $id): ProyectoInfraestructura
    {
        return ProyectoInfraestructura::query()
            ->whereKey($id)
            ->where('propuesta_id', $this->propuestaId)
            ->whereHas('propuesta', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    private function laminaDelUsuario(int $id): Evidencia
    {
        return Evidencia::query()
            ->whereKey($id)
            ->where('propuesta_id', $this->propuestaId)
            ->whereNotNull('proyecto_infraestructura_id')
            ->whereHas('propuesta', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    private function marcarEdicion(Propuesta $propuesta): void
    {
        if (in_array($propuesta->estado, ['BORRADOR', 'GENERADA', 'LISTA'], true)) {
            $propuesta->estado = 'EDITANDO';
        }

        $propuesta->touch();
        $propuesta->save();
    }
}
