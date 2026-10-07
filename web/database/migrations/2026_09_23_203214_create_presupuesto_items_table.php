<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuesto_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->string('concepto', 255);

            $table->text('descripcion')
                ->nullable();

            $table->decimal('cantidad', 12, 2)
                ->default(1);

            $table->decimal('precio_unitario', 15, 2)
                ->default(0);

            $table->string('categoria_gasto', 30)
                ->nullable();

            $table->string('moneda', 10)
                ->default('MXN');

            $table->timestamps();

            $table->index('propuesta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_items');
    }
};
