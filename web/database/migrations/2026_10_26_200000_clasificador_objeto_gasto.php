<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Las categorías de gasto pasan a ser el Clasificador por Objeto del Gasto
 * (CONAC): capítulo, concepto, partida genérica y partida específica, con su
 * definición y tipo de gasto. Se cargan los capítulos 1000 a 5000
 * (database/data/clasificador_objeto_gasto_2026.json).
 *
 * De las 7 categorías anteriores, Personal, Materiales, Servicios y Viáticos
 * están en el clasificador (1000, 2000, 3000 y 375/3751); Equipo,
 * Infraestructura y Otro no, así que se quitan y los conceptos que usaban
 * Equipo quedan «sin categoría» para que el docente elija la partida.
 */
return new class extends Migration
{
    private const ANTERIORES = ['EQUIPO', 'MATERIALES', 'SERVICIOS', 'VIATICOS', 'PERSONAL', 'INFRAESTRUCTURA', 'OTRO'];

    public function up(): void
    {
        Schema::table('categorias_gasto', function (Blueprint $table) {
            $table->string('nivel', 20)->default('PARTIDA_GENERICA')->after('clave');
            $table->string('padre_clave', 10)->nullable()->after('nivel')->index();
            $table->string('tipo_gasto', 20)->default('CORRIENTE')->after('descripcion');
        });

        // Las definiciones del clasificador pasan de 500 caracteres.
        DB::statement('ALTER TABLE categorias_gasto ALTER COLUMN descripcion TYPE text');
        DB::statement('ALTER TABLE categorias_gasto ALTER COLUMN nombre TYPE varchar(255)');

        DB::table('presupuesto_items')->whereIn('categoria_gasto', self::ANTERIORES)->update(['categoria_gasto' => null]);
        DB::table('categorias_gasto')->whereIn('clave', self::ANTERIORES)->delete();

        $datos = json_decode((string) file_get_contents(database_path('data/clasificador_objeto_gasto_2026.json')), true, flags: JSON_THROW_ON_ERROR);
        $ahora = now();

        foreach (array_chunk($datos['partidas'], 200) as $bloque) {
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
            ], $bloque));
        }
    }

    public function down(): void
    {
        DB::table('categorias_gasto')->delete();

        Schema::table('categorias_gasto', function (Blueprint $table) {
            $table->dropColumn(['nivel', 'padre_clave', 'tipo_gasto']);
        });

        $orden = 0;
        foreach (['EQUIPO' => 'Equipo', 'MATERIALES' => 'Materiales', 'SERVICIOS' => 'Servicios', 'VIATICOS' => 'Viáticos', 'PERSONAL' => 'Personal', 'INFRAESTRUCTURA' => 'Infraestructura', 'OTRO' => 'Otro'] as $clave => $nombre) {
            DB::table('categorias_gasto')->insert(['clave' => $clave, 'nombre' => $nombre, 'activa' => true, 'orden' => $orden += 10, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
};
