<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Desde el 18-oct-2026 se publican también becas, premios, concursos,
 * eventos y formación, marcados con su tipo y a quién van dirigidos
 * (INSTITUCION, DOCENTES, ESTUDIANTES). NO_APLICA es interno: resultados,
 * vacantes o no elegibles; no se publica ni entra al historial.
 * Las existentes se clasifican con extractor/scripts/clasificar_tipos.py.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table): void {
            $table->string('tipo', 20)->nullable()->index();
            $table->jsonb('dirigido_a')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table): void {
            $table->dropIndex(['tipo']);
            $table->dropColumn(['tipo', 'dirigido_a']);
        });
    }
};
