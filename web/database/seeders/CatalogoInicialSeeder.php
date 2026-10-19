<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo mínimo para que el motor funcione en una instalación nueva:
 * categoría de investigación, organismos SECIHTI y Secretaría de Ciencia de
 * Yucatán (el enriquecimiento asocia
 * organismos por el dominio de sitio_web) y las fuentes oficiales.
 * Se puede ejecutar varias veces sin duplicar registros.
 */
class CatalogoInicialSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        if (! DB::table('categorias')->whereRaw("LOWER(TRIM(nombre)) LIKE 'investigaci%'")->exists()) {
            DB::table('categorias')->insert([
                'nombre' => 'investigación',
                'descripcion' => 'Financiamiento de proyectos de investigación.',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        DB::table('organismos')->updateOrInsert(
            ['sitio_web' => 'https://secihti.mx'],
            [
                'nombre' => 'Secretaría de Ciencia, Humanidades, Tecnología e Innovación (SECIHTI)',
                'descripcion' => 'Dependencia federal de ciencia y tecnología (antes CONAHCYT).',
                'tipo_organismo' => 'FEDERAL',
                'pais' => 'México',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        );

        // Organismo estatal de la fuente de Yucatán (sin él, el enriquecimiento
        // la confundía con SECIHTI por compartir «Secretaría» y «Ciencia»).
        DB::table('organismos')->updateOrInsert(
            ['sitio_web' => 'https://ciencia.yucatan.gob.mx'],
            [
                'nombre' => 'Secretaría de Ciencia de Yucatán',
                'descripcion' => 'Dependencia del Gobierno del Estado de Yucatán para ciencia, tecnología e innovación.',
                'tipo_organismo' => 'ESTATAL',
                'pais' => 'México',
                'estado' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        );

        $fuentes = [
            // Fuente principal: el spider tiene reglas propias para SECIHTI.
            ['SECIHTI - Convocatorias', 'https://www.secihti.mx/convocatorias/', false, true],
            // Listado genérico (WordPress de la secretaría estatal).
            ['Secretaría de Ciencia de Yucatán - Convocatorias', 'https://ciencia.yucatan.gob.mx/convocatorias/', false, true],
            // Requiere JavaScript y responde 403 (Akamai): el ciclo la omite.
            ['Impulsora de Innovación México Proyectos Ciudadanos', 'https://www.impulsorainnovacion.gob.mx/', true, true],
        ];

        foreach ($fuentes as [$nombre, $url, $javascript, $activa]) {
            if (DB::table('fuentes')->where('url_base', $url)->exists()) {
                continue;
            }

            DB::table('fuentes')->insert([
                'nombre' => $nombre,
                'url_base' => $url,
                'tipo_fuente' => 'WEB',
                'requiere_javascript' => $javascript,
                'activa' => $activa,
                'frecuencia_scraping' => 1440,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }
}
