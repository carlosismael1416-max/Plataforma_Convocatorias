<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as ReglaContrasena;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RecuperacionContrasenaController extends Controller
{
    private const MENSAJE_ENVIO =
        'Si el correo pertenece a una cuenta activa, recibirás un enlace '
        . 'para restablecer tu contraseña. Revisa también tu carpeta de spam.';

    public function solicitar(): View
    {
        return view('auth.olvide-contrasena');
    }

    public function enviar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Solo cuentas activas; el mensaje es el mismo en todos los casos
        // para no revelar qué correos existen.
        // El correo no distingue mayúsculas (Docente@ = docente@).
        $cuenta = User::porCorreo($datos['email']);

        if ($cuenta && $cuenta->estado) {
            Password::sendResetLink(['email' => $cuenta->email]);
        }

        return back()->with('status', self::MENSAJE_ENVIO);
    }

    public function formulario(Request $request, string $token): View
    {
        return view('auth.restablecer-contrasena', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function restablecer(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                ReglaContrasena::min(8)->letters()->numbers(),
            ],
        ]);

        $datos['email'] = User::porCorreo($datos['email'])?->email ?? $datos['email'];

        $estado = Password::reset(
            $datos,
            function (User $usuario, string $contrasena): void {
                $usuario->forceFill([
                    'password' => Hash::make($contrasena),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));
                \App\Services\RegistroActividad::registrar('CONTRASENA', 'Restableció su contraseña con el enlace del correo', usuarioId: $usuario->id);
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.',
            ]);
        }

        return redirect()
            ->route('password.listo')
            ->with('contrasena_actualizada', true);
    }

    /** Confirmación tras guardar la nueva contraseña, con botón al login. */
    public function listo(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('contrasena_actualizada')) {
            return redirect()->route('login');
        }

        return view('auth.contrasena-actualizada');
    }
}
