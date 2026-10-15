<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos iniciales de una instalación nueva: roles, permisos y catálogo.
     * El primer administrador se crea con «php artisan itsva:crear-admin».
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermisoSeeder::class,
            CatalogoInicialSeeder::class,
        ]);
    }
}
