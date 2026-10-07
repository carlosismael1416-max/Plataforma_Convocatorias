<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuestas_base', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->jsonb('contenido_generado')
                ->nullable();

            $table->string('modelo_ia', 150)
                ->nullable();

            $table->text('prompt_utilizado')
                ->nullable();

            $table->unsignedInteger('version')
                ->default(1);

            $table->string('estado', 30)
                ->default('PENDIENTE');

            $table->text('mensaje_error')
                ->nullable();

            $table->timestampTz('fecha_generacion')
                ->nullable();

            $table->timestamps();

            $table->index('convocatoria_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuestas_base');
    }
};
