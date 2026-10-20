<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccesoUnificadoController extends Controller
{
    public function mostrar(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $usuario = Auth::user();

            $destino = $usuario instanceof User
                ? $this->panelDelUsuario($usuario)
                : null;

            if ($destino !== null) {
                return redirect($destino);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.login');
    }

    public function ingresar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => $datos['email'],
            'password' => $datos['password'],
            'estado' => true,
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas o cuenta inactiva.',
            ]);
        }

        $usuario = Auth::user();

        $destino = $usuario instanceof User
            ? $this->panelDelUsuario($usuario)
            : null;

        if ($destino === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta no tiene un rol habilitado.',
            ]);
        }

        $request->session()->regenerate();

        // El acceso anterior se muestra en el perfil («Último acceso»); luego
        // se registra el actual.
        $request->session()->put('acceso_anterior', $usuario->ultimo_acceso?->toIso8601String());
        $usuario->forceFill(['ultimo_acceso' => now()])->saveQuietly();
        \App\Services\RegistroActividad::registrar('INICIO_SESION', 'Inició sesión', usuarioId: $usuario->id);

        return redirect($destino);
    }

    public function salir(Request $request): RedirectResponse
    {
        \App\Services\RegistroActividad::registrar('CIERRE_SESION', 'Cerró sesión');
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function panelDelUsuario(User $usuario): ?string
    {
        if (! $usuario->estado) {
            return null;
        }

        return match ($usuario->role?->nombre) {
            'ADMINISTRADOR' => '/admin',
            'DIRECTIVO' => '/directivo',
            'DOCENTE' => '/docente',
            default => null,
        };
    }
}
