<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuesta_requisitos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->foreignId('convocatoria_requisito_id')
                ->constrained('convocatoria_requisitos')
                ->restrictOnDelete();

            $table->boolean('cumplido')
                ->default(false);

            $table->text('observaciones')
                ->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'propuesta_id',
                    'convocatoria_requisito_id',
                ],
                'propuesta_requisito_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuesta_requisitos');
    }
};
