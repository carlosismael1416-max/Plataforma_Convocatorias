<?php

namespace Tests\Feature;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_hay_un_solo_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('password.request'));

        foreach (['admin', 'directivo', 'docente'] as $panel) {
            $this->get("/{$panel}/login")->assertRedirect('/login');
            $this->get("/{$panel}")->assertRedirect('/login');
        }
    }

    public function test_cada_rol_entra_desde_el_login_unico(): void
    {
        foreach (['ADMINISTRADOR' => '/admin', 'DIRECTIVO' => '/directivo', 'DOCENTE' => '/docente'] as $rol => $panel) {
            $usuario = $this->usuarioConRol($rol);

            $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])
                ->assertRedirect($panel);

            $this->post('/logout');
        }
    }

    public function test_envia_enlace_solo_a_cuentas_activas_con_el_mismo_mensaje(): void
    {
        Notification::fake();

        $activa = $this->usuarioConRol('DOCENTE');
        $inactiva = $this->usuarioConRol('DOCENTE', activo: false);

        $mensajes = [];

        foreach ([$activa->email, $inactiva->email, 'no-existe@itsva.edu.mx'] as $correo) {
            $mensajes[] = $this->from('/recuperar-contrasena')
                ->post('/recuperar-contrasena', ['email' => $correo])
                ->assertRedirect('/recuperar-contrasena')
                ->getSession()
                ->get('status');
        }

        Notification::assertSentTo($activa, ResetPassword::class);
        Notification::assertNotSentTo($inactiva, ResetPassword::class);
        $this->assertCount(1, array_unique($mensajes));
    }

    public function test_el_correo_lleva_al_formulario_en_espanol(): void
    {
        Notification::fake();

        $usuario = $this->usuarioConRol('DOCENTE');
        $this->post('/recuperar-contrasena', ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, ResetPassword::class, function (ResetPassword $aviso) use ($usuario) {
            $correo = $aviso->toMail($usuario);

            $this->get(parse_url($correo->actionUrl, PHP_URL_PATH) . '?' . parse_url($correo->actionUrl, PHP_URL_QUERY))
                ->assertOk()
                ->assertSee('Nueva contraseña');

            return str_contains($correo->subject, 'Restablecer tu contraseña');
        });
    }

    public function test_restablece_la_contrasena_y_permite_entrar(): void
    {
        $usuario = $this->usuarioConRol('DOCENTE');
        $token = Password::createToken($usuario);

        $this->post('/restablecer-contrasena', [
            'token' => $token,
            'email' => $usuario->email,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('NuevaClave2026', $usuario->fresh()->password));

        $this->post('/login', ['email' => $usuario->email, 'password' => 'NuevaClave2026'])
            ->assertRedirect('/docente');
    }

    public function test_token_invalido_o_contrasena_debil_no_cambian_nada(): void
    {
        $usuario = $this->usuarioConRol('DOCENTE');
        $original = $usuario->password;

        $this->post('/restablecer-contrasena', [
            'token' => 'token-falso',
            'email' => $usuario->email,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('email');

        $this->post('/restablecer-contrasena', [
            'token' => Password::createToken($usuario),
            'email' => $usuario->email,
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors('password');

        $this->assertSame($original, $usuario->fresh()->password);
    }
}
