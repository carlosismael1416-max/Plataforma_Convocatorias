<?php

namespace App\Filament\Concerns;

use App\Services\InterruptorMotor;
use Filament\Notifications\Notification;

/**
 * Acción «Encender / Apagar motor» para las páginas del administrador. La
 * autorización se vuelve a comprobar en InterruptorMotor::cambiar().
 */
trait ControlaMotor
{
    public function cambiarMotor(bool $encender): void
    {
        InterruptorMotor::cambiar($encender, auth()->user());

        Notification::make()
            ->title($encender ? 'Motor encendido' : 'Motor apagado')
            ->body($encender
                ? 'El buscador y el motor de extracción vuelven a trabajar en el siguiente ciclo (cada 6 horas).'
                : 'El buscador y el motor de extracción no trabajarán hasta que los vuelvas a encender.')
            ->{$encender ? 'success' : 'warning'}()
            ->send();
    }

    /** Datos para el parcial filament.itsva.interruptor-motor. */
    protected function datosInterruptor(): array
    {
        $ajuste = InterruptorMotor::ajuste();

        return [
            'motorActivo' => InterruptorMotor::activo(),
            'motorCambiadoPor' => $ajuste?->usuario
                ? trim($ajuste->usuario->name . ' ' . ($ajuste->usuario->apellidos ?? ''))
                : null,
            'motorCambiadoEn' => $ajuste?->actualizado_por
                ? $ajuste->updated_at?->setTimezone('America/Merida')->locale('es')->isoFormat('D MMM YYYY, HH:mm')
                : null,
        ];
    }
}
