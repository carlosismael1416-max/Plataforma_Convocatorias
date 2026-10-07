<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesamientos_documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_archivo_id')
                ->constrained('convocatoria_archivos')
                ->cascadeOnDelete();

            $table->foreignId('ejecucion_fuente_id')
                ->nullable()
                ->constrained('ejecucion_fuentes')
                ->nullOnDelete();

            $table->string('motor_extraccion', 50);

            $table->boolean('requiere_ocr')
                ->default(false);

            $table->unsignedInteger('paginas')
                ->nullable();

            $table->string('estado', 30)
                ->default('PENDIENTE');

            $table->text('texto_extraido')
                ->nullable();

            $table->jsonb('datos_extraidos')
                ->nullable();

            $table->string('hash_documento', 128)
                ->nullable();

            $table->text('mensaje_error')
                ->nullable();

            $table->timestampTz('fecha_inicio')
                ->nullable();

            $table->timestampTz('fecha_fin')
                ->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('hash_documento');
            $table->index('convocatoria_archivo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesamientos_documentos');
    }
};
