<?php

namespace Tests;

use App\Models\Convocatoria;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // RefreshDatabase vacía la base: nunca contra una que no sea de pruebas.
        $conexion = $app['config']->get('database.default');
        $base = (string) $app['config']->get("database.connections.{$conexion}.database");

        if (! str_ends_with($base, '_test')) {
            throw new RuntimeException(
                "Las pruebas solo corren en una base *_test (se obtuvo: {$base})."
            );
        }

        return $app;
    }

    protected function usuarioConRol(string $rol, bool $activo = true): User
    {
        if (! Role::query()->where('nombre', $rol)->exists()) {
            $this->seed(RoleSeeder::class);
        }

        return User::factory()->create([
            'role_id' => Role::query()->where('nombre', $rol)->value('id'),
            'estado' => $activo,
        ]);
    }

    protected function convocatoria(array $atributos = []): Convocatoria
    {
        return Convocatoria::query()->create($atributos + [
            'titulo' => 'Convocatoria de prueba',
            'descripcion' => 'Descripción',
            'estado' => 'PUBLICADA',
            'origen' => 'MANUAL',
            'url_original' => 'https://secihti.mx/prueba/'.uniqid(),
        ]);
    }
}
