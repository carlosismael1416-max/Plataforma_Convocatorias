<?php

namespace App\Filament\Docente;

use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\UsuarioConvocatoria;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.docente.dashboard';

    protected static ?string $title = 'Dashboard';

    public string $nombreUsuario = 'Docente';

    public int $convocatoriasDisponibles = 0;

    public int $misConvocatorias = 0;

    public int $misPropuestas = 0;

    public int $proximasACerrar = 0;

    public array $convocatoriasRecientes = [];

    public array $proximosCierres = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $nombreCompleto = trim(
            $user->name . ' ' . ($user->apellidos ?? '')
        );

        $this->nombreUsuario = $nombreCompleto !== ''
            ? $nombreCompleto
            : 'Docente';

        $estadosDisponibles = [
            'PUBLICADA',
            'ACTIVA',
        ];

        $consultaDisponibles = Convocatoria::query()
            ->whereIn('estado', $estadosDisponibles);

        /*
        |--------------------------------------------------------------------------
        | Estadísticas
        |--------------------------------------------------------------------------
        */

        $this->convocatoriasDisponibles = (clone $consultaDisponibles)
            ->count();

        $this->misConvocatorias = UsuarioConvocatoria::query()
            ->where('user_id', $user->id)
            ->count();

        $this->misPropuestas = Propuesta::query()
            ->where('user_id', $user->id)
            ->count();

        $this->proximasACerrar = (clone $consultaDisponibles)
            ->whereNotNull('fecha_cierre')
            ->whereDate('fecha_cierre', '>=', today())
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Convocatorias recientes disponibles
        |--------------------------------------------------------------------------
        */

        $this->convocatoriasRecientes = (clone $consultaDisponibles)
            ->with([
                'organismo',
                'categoria',
            ])
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(function (Convocatoria $convocatoria): array {
                return [
                    'id' => $convocatoria->id,

                    'titulo' => $convocatoria->titulo,

                    'organismo' =>
                        $convocatoria->organismo?->nombre
                        ?? 'Sin organismo',

                    'categoria' =>
                        $convocatoria->categoria?->nombre
                        ?? 'Sin categoría',

                    'monto' => $this->formatearMonto(
                        $convocatoria->monto_maximo,
                        $convocatoria->moneda
                    ),

                    'estado' => $convocatoria->estado,
                ];
            })
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Próximos cierres
        |--------------------------------------------------------------------------
        */

        $this->proximosCierres = (clone $consultaDisponibles)
            ->with('organismo')
            ->whereNotNull('fecha_cierre')
            ->whereDate('fecha_cierre', '>=', today())
            ->orderBy('fecha_cierre')
            ->limit(3)
            ->get()
            ->map(function (Convocatoria $convocatoria): array {
                $fecha = $convocatoria->fecha_cierre;

                return [
                    'id' => $convocatoria->id,

                    'titulo' => $convocatoria->titulo,

                    'organismo' =>
                        $convocatoria->organismo?->nombre
                        ?? 'Sin organismo',

                    'dia' => $fecha?->format('d') ?? '--',

                    'mes' => $fecha
                        ? $this->nombreMes((int) $fecha->format('n'))
                        : '---',

                    'fecha' => $fecha?->format('d/m/Y'),
                ];
            })
            ->all();
    }

    private function formatearMonto(
        mixed $monto,
        ?string $moneda
    ): string {
        if ($monto === null) {
            return 'Monto no especificado';
        }

        return '$'
            . number_format((float) $monto, 2, '.', ',')
            . ' '
            . ($moneda ?: 'MXN');
    }

    private function nombreMes(int $mes): string
    {
        return match ($mes) {
            1 => 'ENE',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'ABR',
            5 => 'MAY',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGO',
            9 => 'SEP',
            10 => 'OCT',
            11 => 'NOV',
            12 => 'DIC',
            default => '---',
        };
    }
}
