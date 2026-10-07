<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejecuciones_scraping', function (Blueprint $table) {
            $table->id();

            $table->timestampTz('fecha_inicio')
                ->useCurrent();

            $table->timestampTz('fecha_fin')
                ->nullable();

            $table->string('estado', 40)
                ->default('INICIADO');

            $table->unsignedInteger('total_fuentes')
                ->default(0);

            $table->unsignedInteger('fuentes_exitosas')
                ->default(0);

            $table->unsignedInteger('fuentes_fallidas')
                ->default(0);

            $table->unsignedInteger('total_encontradas')
                ->default(0);

            $table->unsignedInteger('nuevas_convocatorias')
                ->default(0);

            $table->unsignedInteger('convocatorias_actualizadas')
                ->default(0);

            $table->unsignedInteger('duplicados_detectados')
                ->default(0);

            $table->unsignedInteger('total_errores')
                ->default(0);

            $table->decimal('duracion_segundos', 12, 2)
                ->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_scraping');
    }
};
