<?php

namespace App\Models\Concerns;

use App\Services\RegistroActividad;

/**
 * Anota en «actividades» quién crea, edita o borra el modelo desde la web.
 * El motor escribe con SQL directo, así que no genera ruido aquí.
 * Cada modelo define accionActividad() y nombreActividad().
 */
trait RegistraActividad
{
    public static function bootRegistraActividad(): void
    {
        static::created(fn ($m) => $m->anotarActividad('Creó'));

        static::updated(function ($m): void {
            $cambios = array_diff(array_keys($m->getChanges()), ['updated_at', 'ultimo_acceso', 'remember_token', 'fecha_actualizacion', 'password']);

            if ($cambios !== []) {
                $m->anotarActividad('Editó', agrupar: true);
            }
        });

        static::deleted(fn ($m) => $m->anotarActividad('Eliminó'));
    }

    protected function anotarActividad(string $verbo, bool $agrupar = false): void
    {
        if (! auth()->check() || RegistroActividad::$silenciado) {
            return;
        }

        [$accion, $sustantivo] = $this->accionActividad();

        RegistroActividad::registrar(
            $accion,
            "{$verbo} {$sustantivo} «" . mb_strimwidth($this->nombreActividad(), 0, 160, '…') . '»',
            $this,
            agrupar: $agrupar,
        );
    }

    /** @return array{0: string, 1: string} [acción, «la propuesta»…] */
    abstract protected function accionActividad(): array;

    abstract protected function nombreActividad(): string;
}
