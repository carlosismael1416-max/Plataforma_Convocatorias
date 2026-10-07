<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatoria_apoyos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->string('clave', 80);
            $table->string('concepto', 160);

            // Por ejemplo: MEXICANO o FRANCES.
            $table->string('componente', 40);

            $table->decimal('monto_maximo', 18, 2);
            $table->string('moneda', 3);

            // PROYECTO_TOTAL, ETAPA_ANUAL,
            // PASAJE, DIA, etc.
            $table->string('unidad', 60);

            // Permite indicar que un monto es
            // parte de otro y no debe sumarse.
            // Ejemplo: la etapa anual forma parte
            // del apoyo máximo del proyecto.
            $table->string('incluido_en_clave', 80)
                ->nullable();

            // Condiciones como número de etapas,
            // límites de días o beneficiarios.
            $table->jsonb('condiciones')
                ->nullable();

            // Nombre, URL, página y fragmento
            // de los documentos originales.
            $table->jsonb('fuentes_documentales')
                ->nullable();

            $table->string('estado_documental', 40)
                ->default('PENDIENTE_REVISION');

            $table->timestamps();

            $table->unique(
                ['convocatoria_id', 'clave'],
                'uq_convocatoria_apoyo_clave'
            );
        });

        DB::statement(
            'ALTER TABLE convocatoria_apoyos
             ADD CONSTRAINT chk_apoyo_monto
             CHECK (monto_maximo >= 0)'
        );

        DB::statement(
            'ALTER TABLE convocatoria_apoyos
             ADD CONSTRAINT chk_apoyo_no_autoreferencia
             CHECK (
                 incluido_en_clave IS NULL
                 OR incluido_en_clave <> clave
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatoria_apoyos');
    }
};
