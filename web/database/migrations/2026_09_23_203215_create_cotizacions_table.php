<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            $table->foreignId('presupuesto_item_id')
                ->nullable()
                ->constrained('presupuesto_items')
                ->nullOnDelete();

            $table->string('proveedor', 255)
                ->nullable();

            $table->string('concepto', 255)
                ->nullable();

            $table->decimal('monto', 15, 2);

            $table->string('moneda', 10)
                ->default('MXN');

            $table->text('archivo_url')
                ->nullable();

            $table->date('fecha_cotizacion')
                ->nullable();

            $table->timestamps();

            $table->index('propuesta_id');
            $table->index('presupuesto_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
