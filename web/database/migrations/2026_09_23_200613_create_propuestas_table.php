<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuestas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->restrictOnDelete();

            $table->foreignId('propuesta_base_id')
                ->nullable()
                ->constrained('propuestas_base')
                ->nullOnDelete();

            $table->string('titulo', 500);

            $table->text('resumen')
                ->nullable();

            $table->text('justificacion')
                ->nullable();

            $table->text('metodologia')
                ->nullable();

            $table->text('impacto_esperado')
                ->nullable();

            $table->string('estado', 30)
                ->default('BORRADOR');

            $table->unsignedInteger('version')
                ->default(1);

            $table->timestampTz('fecha_envio')
                ->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('convocatoria_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuestas');
    }
};
