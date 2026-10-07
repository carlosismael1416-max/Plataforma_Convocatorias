<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->foreignId('propuesta_requisito_id')
                ->nullable()
                ->constrained('propuesta_requisitos')
                ->nullOnDelete();

            $table->foreignId('propuesta_entregable_id')
                ->nullable()
                ->constrained('propuesta_entregables')
                ->nullOnDelete();

            $table->string('nombre', 255);

            $table->text('descripcion')
                ->nullable();

            $table->text('ruta_archivo')
                ->nullable();

            $table->text('url_archivo')
                ->nullable();

            $table->timestamps();

            $table->index('propuesta_id');
            $table->index('propuesta_requisito_id');
            $table->index('propuesta_entregable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
