<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de la revisión de propuestas: cada envío del autor y cada
 * dictamen del directivo (con sus observaciones) queda registrado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuesta_revisiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('propuesta_id')
                ->constrained('propuestas')
                ->cascadeOnDelete();

            // Quien envía (autor) o dictamina (directivo).
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('accion', 20);

            $table->string('estado_anterior', 30);

            $table->string('estado_nuevo', 30);

            $table->text('observaciones')
                ->nullable();

            $table->timestamps();

            $table->index(['propuesta_id', 'created_at']);
        });

        DB::statement("
            ALTER TABLE propuesta_revisiones
            ADD CONSTRAINT chk_propuesta_revisiones_accion
            CHECK (accion IN ('ENVIAR', 'APROBAR', 'DEVOLVER', 'RECHAZAR'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('propuesta_revisiones');
    }
};
