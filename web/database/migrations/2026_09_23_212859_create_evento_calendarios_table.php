<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_calendario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('convocatoria_id')
                ->nullable()
                ->constrained('convocatorias')
                ->nullOnDelete();

            $table->string('titulo', 255);

            $table->text('descripcion')
                ->nullable();

            $table->timestampTz('fecha_inicio');

            $table->timestampTz('fecha_fin')
                ->nullable();

            $table->string('tipo_evento', 30)
                ->default('OTRO');

            $table->boolean('recordatorio')
                ->default(false);

            $table->unsignedInteger('minutos_recordatorio')
                ->nullable();

            $table->boolean('estado')
                ->default(true);

            $table->timestamps();

            $table->index('user_id');
            $table->index('convocatoria_id');
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_calendario');
    }
};
