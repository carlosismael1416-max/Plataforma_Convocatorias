<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capítulo 6000 (Inversión pública) del Clasificador por Objeto del Gasto:
 * obra nueva, continuidad y adecuaciones de las propuestas de
 * infraestructura (FAM) se cargan aquí. Mismo documento que los capítulos
 * 1000 a 5000 (database/data/clasificador_objeto_gasto_2026_cap6000.json).
 * Solo agrega las claves que falten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $datos = json_decode((string) file_get_contents(database_path('data/clasificador_objeto_gasto_2026_cap6000.json')), true, flags: JSON_THROW_ON_ERROR);
        $existentes = DB::table('categorias_gasto')->pluck('clave')->map(fn ($c) => (string) $c)->all();
        $ahora = now();

        $nuevas = array_filter($datos['partidas'], fn (array $p) => ! in_array((string) $p['clave'], $existentes, true));

        DB::table('categorias_gasto')->insert(array_map(fn (array $p) => [
            'clave' => $p['clave'],
            'nivel' => $p['nivel'],
            'padre_clave' => $p['padre'],
            'nombre' => $p['nombre'],
            'descripcion' => $p['descripcion'],
            'tipo_gasto' => $p['tipo_gasto'],
            'activa' => true,
            'orden' => $p['orden'],
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ], array_values($nuevas)));
    }

    public function down(): void
    {
        // Solo las del capítulo que no se hayan usado en un presupuesto.
        $usadas = DB::table('presupuesto_items')->whereNotNull('categoria_gasto')->pluck('categoria_gasto')->all();

        DB::table('categorias_gasto')
            ->where('clave', 'like', '6%')
            ->whereNotIn('clave', $usadas)
            ->delete();
    }
};
