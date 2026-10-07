<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuesta_entregables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->foreignId('cronograma_actividad_id')
                ->nullable()
                ->constrained('cronograma_actividades')
                ->nullOnDelete();

            $table->string('nombre', 255);

            $table->text('descripcion')
                ->nullable();

            $table->date('fecha_limite')
                ->nullable();

            $table->string('estado', 30)
                ->default('PENDIENTE');

            $table->timestampTz('fecha_entrega')
                ->nullable();

            $table->timestamps();

            $table->index('propuesta_id');
            $table->index('cronograma_actividad_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuesta_entregables');
    }
};
