<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** El correo no lo usa otra cuenta, sin distinguir mayúsculas (Docente@ = docente@). */
class CorreoDisponible implements ValidationRule
{
    public function __construct(private ?int $ignorarUsuarioId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $usado = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim((string) $value))])
            ->when($this->ignorarUsuarioId, fn ($q) => $q->whereKeyNot($this->ignorarUsuarioId))
            ->exists();

        if ($usado) {
            $fail('Ese correo ya pertenece a otra cuenta.');
        }
    }
}
