<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seguimiento de convocatorias publicadas: lo último que el extractor vio en
 * la página oficial (texto, documentos y fecha de cierre) para detectar
 * cambios y avisar a quienes la siguen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatoria_seguimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->unique()
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->text('texto')->nullable();

            $table->jsonb('documentos')->default('[]');

            $table->date('fecha_cierre_web')->nullable();

            $table->timestampTz('revisado_en')->nullable();

            $table->timestampTz('ultimo_cambio_en')->nullable();

            // INICIAL, SIN_CAMBIOS, CAMBIOS, ERROR
            $table->string('ultimo_resultado', 30)->nullable();

            $table->text('ultimo_resumen')->nullable();

            $table->timestamps();

            $table->index('revisado_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatoria_seguimientos');
    }
};
