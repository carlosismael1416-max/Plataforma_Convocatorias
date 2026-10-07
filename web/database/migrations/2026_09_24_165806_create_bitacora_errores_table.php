<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_errores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ejecucion_scraping_id')
                ->nullable()
                ->constrained('ejecuciones_scraping')
                ->nullOnDelete();

            $table->foreignId('ejecucion_fuente_id')
                ->nullable()
                ->constrained('ejecucion_fuentes')
                ->nullOnDelete();

            $table->foreignId('fuente_id')
                ->nullable()
                ->constrained('fuentes')
                ->nullOnDelete();

            $table->string('tipo_error', 150)->nullable();
            $table->string('codigo_error', 100)->nullable();

            $table->text('mensaje');
            $table->text('detalle')->nullable();
            $table->text('stack_trace')->nullable();
            $table->text('url')->nullable();

            $table->boolean('resuelto')->default(false);

            $table->timestampTz('fecha_resolucion')->nullable();

            $table->foreignId('user_id_resolucion')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('resuelto');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_errores');
    }
};
