<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de actividad de los usuarios en la plataforma: inicios de
 * sesión, propuestas, convocatorias subidas, cambios de administración…
 * Lo muestran «Actividad reciente» de cada perfil, el historial de
 * actividades del admin y el historial de movimientos de propuestas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 40);
            $table->string('descripcion', 500);
            $table->string('sujeto_tipo', 40)->nullable();
            $table->unsignedBigInteger('sujeto_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['sujeto_tipo', 'sujeto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
