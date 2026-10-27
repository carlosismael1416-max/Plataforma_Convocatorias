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
    /** Cuentas con las que se inició sesión en este navegador (para «Cambiar de cuenta»). */
    public const CUENTAS_SESION = 'cuentas_recordadas';

    public function mostrar(Request $request): View|RedirectResponse
    {
        // «Agregar otra cuenta»: el login se muestra sin cerrar la sesión actual.
        if (Auth::check() && $request->boolean('agregar')) {
            return view('auth.login', ['agregando' => Auth::user()]);
        }

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

        // El correo no distingue mayúsculas: se usa el que tiene guardado la cuenta.
        $cuenta = User::porCorreo($datos['email']);

        if (! Auth::attempt([
            'email' => $cuenta?->email ?? $datos['email'],
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
        $this->recordarCuenta($request, $usuario);

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

    /**
     * Cambia a otra cuenta con la que ya se inició sesión en este navegador
     * (alguien con cuenta de docente y otra de administrador, por ejemplo).
     * No pide la contraseña otra vez: ya se comprobó al agregarla. «Cerrar
     * sesión» olvida todas.
     */
    public function cambiarCuenta(Request $request, User $usuario): RedirectResponse
    {
        $recordadas = (array) $request->session()->get(self::CUENTAS_SESION, []);

        abort_unless(in_array($usuario->id, $recordadas, true), 403);

        $destino = $this->panelDelUsuario($usuario);

        if ($destino === null) {
            $request->session()->put(self::CUENTAS_SESION, array_values(array_diff($recordadas, [$usuario->id])));

            return back()->with('error_cuenta', 'Esa cuenta ya no está activa.');
        }

        Auth::login($usuario);
        $request->session()->regenerate();
        $request->session()->put(self::CUENTAS_SESION, $recordadas);
        \App\Services\RegistroActividad::registrar('INICIO_SESION', 'Cambió a esta cuenta desde otra sesión abierta', usuarioId: $usuario->id);

        return redirect($destino);
    }

    private function recordarCuenta(Request $request, User $usuario): void
    {
        $recordadas = (array) $request->session()->get(self::CUENTAS_SESION, []);
        $recordadas[] = $usuario->id;
        $request->session()->put(self::CUENTAS_SESION, array_values(array_unique($recordadas)));
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
