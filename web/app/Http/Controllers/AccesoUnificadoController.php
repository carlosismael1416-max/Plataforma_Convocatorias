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
        ])) {
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

        return redirect($destino);
    }

    public function salir(Request $request): RedirectResponse
    {
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
