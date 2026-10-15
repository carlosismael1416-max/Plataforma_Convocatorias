<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstalacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_seeder_carga_el_catalogo_sin_duplicar(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(4, DB::table('roles')->count());
        $this->assertSame(1, DB::table('categorias')->count());
        $this->assertSame(1, DB::table('organismos')->where('sitio_web', 'https://secihti.mx')->count());
        $this->assertSame(3, DB::table('fuentes')->count());
        $this->assertTrue(DB::table('ajustes_sistema')->where('clave', 'motor_activo')->value('valor') === '1');
    }

    public function test_crear_admin_y_entrar(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('itsva:crear-admin', ['email' => 'Admin@ITSVA.edu.mx', '--nombre' => 'Admin'])
            ->expectsQuestion('Contraseña (mínimo 8, con letras y números)', 'ClaveSegura2026')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'admin@itsva.edu.mx')->firstOrFail();
        $this->assertTrue($admin->tieneRol('ADMINISTRADOR'));
        $this->assertTrue($admin->tienePermiso('ejecutar_scraping'));

        $this->post('/login', ['email' => 'admin@itsva.edu.mx', 'password' => 'ClaveSegura2026'])
            ->assertRedirect('/admin');
    }

    public function test_crear_admin_rechaza_contrasena_debil(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('itsva:crear-admin', ['email' => 'admin@itsva.edu.mx'])
            ->expectsQuestion('Contraseña (mínimo 8, con letras y números)', 'corta')
            ->assertFailed();

        $this->assertFalse(User::query()->where('email', 'admin@itsva.edu.mx')->exists());
    }
}
