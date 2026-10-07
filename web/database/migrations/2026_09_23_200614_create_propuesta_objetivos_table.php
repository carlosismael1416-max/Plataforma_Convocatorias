<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuesta_objetivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->string('tipo', 20);

            $table->text('descripcion');

            $table->unsignedInteger('orden')
                ->default(0);

            $table->timestamps();

            $table->index('propuesta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuesta_objetivos');
    }
};
