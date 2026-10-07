<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatoria_requisitos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->string('titulo', 255)->nullable();

            $table->text('descripcion');

            $table->boolean('obligatorio')
                ->default(true);

            $table->string('tipo_requisito', 100)
                ->nullable();

            $table->unsignedInteger('orden')
                ->default(0);

            $table->timestamps();

            $table->index('convocatoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatoria_requisitos');
    }
};
