<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejecucion_fuentes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ejecucion_scraping_id')
                ->constrained('ejecuciones_scraping')
                ->cascadeOnDelete();

            $table->foreignId('fuente_id')
                ->constrained('fuentes')
                ->restrictOnDelete();

            $table->timestampTz('fecha_inicio')
                ->useCurrent();

            $table->timestampTz('fecha_fin')
                ->nullable();

            $table->string('estado', 30)
                ->default('PENDIENTE');

            $table->unsignedInteger('registros_encontrados')
                ->default(0);

            $table->unsignedInteger('registros_nuevos')
                ->default(0);

            $table->unsignedInteger('registros_actualizados')
                ->default(0);

            $table->unsignedInteger('duplicados')
                ->default(0);

            $table->unsignedInteger('errores')
                ->default(0);

            $table->unsignedSmallInteger('http_status')
                ->nullable();

            $table->decimal('duracion_segundos', 12, 2)
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['ejecucion_scraping_id', 'fuente_id'],
                'ejecucion_fuente_unique'
            );

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejecucion_fuentes');
    }
};
