<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('convocatoria_id')
                ->nullable()
                ->constrained('convocatorias')
                ->nullOnDelete();

            $table->foreignId('propuesta_id')
                ->nullable()
                ->constrained('propuestas')
                ->nullOnDelete();

            $table->string('titulo', 255);

            $table->text('mensaje');

            $table->string('tipo', 50)
                ->default('SISTEMA');

            $table->boolean('leida')
                ->default(false);

            $table->timestampTz('fecha_lectura')
                ->nullable();

            $table->timestamps();

            $table->index(['user_id', 'leida']);
            $table->index('convocatoria_id');
            $table->index('propuesta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
