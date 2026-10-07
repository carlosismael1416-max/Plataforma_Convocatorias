<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            [
                'nombre' => 'ver_convocatorias',
                'descripcion' => 'Consultar convocatorias',
                'modulo' => 'CONVOCATORIAS',
            ],
            [
                'nombre' => 'crear_convocatorias',
                'descripcion' => 'Crear convocatorias',
                'modulo' => 'CONVOCATORIAS',
            ],
            [
                'nombre' => 'editar_convocatorias',
                'descripcion' => 'Editar convocatorias',
                'modulo' => 'CONVOCATORIAS',
            ],
            [
                'nombre' => 'eliminar_convocatorias',
                'descripcion' => 'Eliminar convocatorias',
                'modulo' => 'CONVOCATORIAS',
            ],
            [
                'nombre' => 'aprobar_convocatorias',
                'descripcion' => 'Aprobar convocatorias',
                'modulo' => 'CONVOCATORIAS',
            ],
            [
                'nombre' => 'gestionar_usuarios',
                'descripcion' => 'Administrar usuarios',
                'modulo' => 'USUARIOS',
            ],
            [
                'nombre' => 'gestionar_fuentes',
                'descripcion' => 'Administrar fuentes web',
                'modulo' => 'EXTRACCION',
            ],
            [
                'nombre' => 'ejecutar_scraping',
                'descripcion' => 'Ejecutar el motor de extracción',
                'modulo' => 'EXTRACCION',
            ],
            [
                'nombre' => 'ver_bitacora',
                'descripcion' => 'Consultar la bitácora de errores',
                'modulo' => 'EXTRACCION',
            ],
            [
                'nombre' => 'generar_propuestas',
                'descripcion' => 'Generar propuestas',
                'modulo' => 'PROPUESTAS',
            ],
            [
                'nombre' => 'editar_propuestas',
                'descripcion' => 'Editar propuestas',
                'modulo' => 'PROPUESTAS',
            ],
            [
                'nombre' => 'generar_reportes',
                'descripcion' => 'Generar reportes',
                'modulo' => 'REPORTES',
            ],
            [
                'nombre' => 'ver_estadisticas',
                'descripcion' => 'Consultar estadísticas',
                'modulo' => 'REPORTES',
            ],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        $docente = Role::where('nombre', 'DOCENTE')->firstOrFail();

        $docente->permisos()->sync(
            Permiso::whereIn('nombre', [
                'ver_convocatorias',
                'generar_propuestas',
                'editar_propuestas',
            ])->pluck('id')
        );

        $directivo = Role::where('nombre', 'DIRECTIVO')->firstOrFail();

        $directivo->permisos()->sync(
            Permiso::whereIn('nombre', [
                'ver_convocatorias',
                'aprobar_convocatorias',
                'generar_reportes',
                'ver_estadisticas',
            ])->pluck('id')
        );

        $administrador = Role::where(
            'nombre',
            'ADMINISTRADOR'
        )->firstOrFail();

        $administrador->permisos()->sync(
            Permiso::pluck('id')
        );

        $sistema = Role::where('nombre', 'SISTEMA')->firstOrFail();

        $sistema->permisos()->sync(
            Permiso::whereIn('nombre', [
                'crear_convocatorias',
                'editar_convocatorias',
                'ejecutar_scraping',
            ])->pluck('id')
        );
    }
}
