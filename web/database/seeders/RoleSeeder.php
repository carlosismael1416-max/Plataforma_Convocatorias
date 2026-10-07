<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        DB::table('roles')->upsert([
            [
                'nombre' => 'DOCENTE',
                'descripcion' => 'Usuario docente de la plataforma',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'nombre' => 'DIRECTIVO',
                'descripcion' => 'Usuario encargado de revisar convocatorias y consultar reportes',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'nombre' => 'ADMINISTRADOR',
                'descripcion' => 'Administrador general de la plataforma',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'nombre' => 'SISTEMA',
                'descripcion' => 'Procesos automáticos del sistema',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ], ['nombre'], [
            'descripcion',
            'estado',
            'updated_at',
        ]);
    }
}
