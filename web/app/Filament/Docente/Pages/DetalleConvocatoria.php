<?php

namespace App\Filament\Docente\Pages;

use App\Filament\Concerns\GestionaMisConvocatorias;
use App\Filament\Concerns\RolDelPanel;
use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use Filament\Pages\Page;
use Filament\Panel;

class DetalleConvocatoria extends Page
{
    use GestionaMisConvocatorias;

    use RolDelPanel;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug =
        'detalle-convocatoria/{record}';

    protected static ?string $title =
        'Detalle de convocatoria';

    protected string $view =
        'filament.docente.pages.detalle-convocatoria';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    public Convocatoria $convocatoria;

    public string $tab = 'informacion';

    public bool $guardada = false;

    public bool $favorita = false;

    // Vigente: se puede guardar y preparar propuesta. Si no, es historial.
    public bool $disponible = false;

    public bool $tienePropuesta = false;

    public static function getRelativeRouteName(
        Panel $panel
    ): string {
        return 'detalle-convocatoria';
    }

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(static::rolDelPanel());
    }

    public function mount(int|string $record): void
    {
        // Vigentes y el historial (cerradas, solo de consulta). Lo que no
        // pasó el validador es interno del motor.
        $this->convocatoria = Convocatoria::query()
            ->whereKey($record)
            ->where(
                fn ($q) => $q
                    ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
                    ->orWhere('estado', 'CERRADA')
            )
            ->with([
                'categoria',
                'organismo',
                'fuente',
                'seguimiento',
                'requisitos',
                'archivos',
                'financiamientos',
                'modalidadesFinanciamiento.etapas',
                'apoyosFinancieros',
            ])
            ->firstOrFail();

        $this->despuesDeCambiarSeguimiento();
    }

    /** Compatibilidad con la vista anterior. */
    public function guardarConvocatoria(): void
    {
        $this->guardarEnMis($this->convocatoria->id);
    }

    protected function despuesDeCambiarSeguimiento(): void
    {
        $usuarioId = auth()->id();

        $this->disponible = Convocatoria::query()
            ->visibles()
            ->whereKey($this->convocatoria->id)
            ->exists();

        $seguimiento = UsuarioConvocatoria::query()
            ->where('user_id', $usuarioId)
            ->where('convocatoria_id', $this->convocatoria->id)
            ->first();

        $this->guardada = $seguimiento !== null;
        $this->favorita = (bool) $seguimiento?->es_favorita;

        $this->tienePropuesta = Propuesta::query()
            ->where('user_id', $usuarioId)
            ->where('convocatoria_id', $this->convocatoria->id)
            ->exists();
    }
}
