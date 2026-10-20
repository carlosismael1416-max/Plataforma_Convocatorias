<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Sirve la foto de perfil de un usuario (solo con sesión iniciada). */
class FotoPerfilController extends Controller
{
    public function __invoke(User $usuario): Response
    {
        abort_unless($usuario->foto && Storage::disk('local')->exists($usuario->foto), 404);

        return Storage::disk('local')->response($usuario->foto, null, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
