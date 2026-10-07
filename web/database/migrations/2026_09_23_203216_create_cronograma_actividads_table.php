<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronograma_actividades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->string('actividad', 255);

            $table->text('descripcion')
                ->nullable();

            $table->date('fecha_inicio');

            $table->date('fecha_fin');

            $table->string('responsable', 255)
                ->nullable();

            $table->string('estado', 30)
                ->default('PENDIENTE');

            $table->decimal('porcentaje_avance', 5, 2)
                ->default(0);

            $table->unsignedInteger('orden')
                ->default(0);

            $table->timestamps();

            $table->index('propuesta_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronograma_actividades');
    }
};
