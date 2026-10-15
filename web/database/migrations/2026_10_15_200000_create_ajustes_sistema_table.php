<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustes del sistema que se cambian desde la web. Primer uso: el
 * interruptor del buscador y motor de extracción (motor_activo), que el ciclo
 * automático y la API del extractor consultan antes de trabajar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes_sistema', function (Blueprint $table) {
            $table->string('clave', 60)->primary();

            $table->string('valor', 255);

            $table->foreignId('actualizado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestampsTz();
        });

        DB::table('ajustes_sistema')->insert([
            'clave' => 'motor_activo',
            'valor' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_sistema');
    }
};
