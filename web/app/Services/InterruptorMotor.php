<?php

namespace App\Services;

use App\Models\AjusteSistema;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Interruptor del buscador y motor de extracción. El ciclo automático
 * (extractor/app/ejecutar_ciclo_automatico.py) y la API del extractor lo
 * consultan antes de trabajar; apagado, no buscan ni extraen nada.
 */
class InterruptorMotor
{
    private const CLAVE = 'motor_activo';

    public static function activo(): bool
    {
        // Sin fila, el motor está encendido (mismo criterio que el extractor).
        return AjusteSistema::query()->whereKey(self::CLAVE)->value('valor') !== '0';
    }

    public static function ajuste(): ?AjusteSistema
    {
        return AjusteSistema::query()->with('usuario:id,name,apellidos')->find(self::CLAVE);
    }

    public static function cambiar(bool $encendido, User $usuario): void
    {
        if (
            ! $usuario->estado
            || ! $usuario->tieneRol('ADMINISTRADOR')
            || ! $usuario->tienePermiso('ejecutar_scraping')
        ) {
            throw new AuthorizationException('No tienes permiso para encender o apagar el motor.');
        }

        AjusteSistema::query()->updateOrCreate(
            ['clave' => self::CLAVE],
            ['valor' => $encendido ? '1' : '0', 'actualizado_por' => $usuario->id],
        );
    }
}
