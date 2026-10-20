<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de reportes exportados en Reportes (directivo): quién, qué
 * tipo, con qué filtros y formato, y cuántos registros. Permite volver a
 * generar uno con los mismos filtros.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->string('formato', 10);
            $table->jsonb('filtros');
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
