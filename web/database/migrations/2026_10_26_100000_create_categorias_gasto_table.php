<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Categorías de gasto del presupuesto de una propuesta, administrables por
 * el administrador y el directivo («Gestión de propuestas»). Antes eran
 * fijas en el código; presupuesto_items.categoria_gasto sigue guardando la
 * clave, así que los conceptos ya capturados no cambian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_gasto', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 40)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 500)->nullable();
            $table->boolean('activa')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        $orden = 0;
        foreach ([
            'EQUIPO' => 'Equipo',
            'MATERIALES' => 'Materiales',
            'SERVICIOS' => 'Servicios',
            'VIATICOS' => 'Viáticos',
            'PERSONAL' => 'Personal',
            'INFRAESTRUCTURA' => 'Infraestructura',
            'OTRO' => 'Otro',
        ] as $clave => $nombre) {
            DB::table('categorias_gasto')->insert([
                'clave' => $clave,
                'nombre' => $nombre,
                'activa' => true,
                'orden' => $orden += 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_gasto');
    }
};
