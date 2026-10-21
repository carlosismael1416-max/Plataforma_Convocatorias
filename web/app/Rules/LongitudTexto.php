<?php

namespace App\Rules;

use App\Support\TextoEnriquecido;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Máximo de caracteres de un campo con formato: cuenta el texto, no las etiquetas. */
class LongitudTexto implements ValidationRule
{
    public function __construct(private int $maximo) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (TextoEnriquecido::longitud((string) $value) > $this->maximo) {
            $fail('El texto no debe pasar de ' . number_format($this->maximo) . ' caracteres.');
        }
    }
}
