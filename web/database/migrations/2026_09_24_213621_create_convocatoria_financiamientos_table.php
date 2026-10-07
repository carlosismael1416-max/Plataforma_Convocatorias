<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatoria_financiamientos', function (Blueprint $table) {
            $table->id();

            // Convocatoria a la que pertenece el financiamiento.
            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            // Identificador del grupo: por ejemplo, 1,2,3,6.
            $table->string('grupo_clave', 100);

            // Ejes estratégicos del grupo, guardados como JSON.
            $table->jsonb('ejes_estrategicos');

            // Primera etapa de financiamiento.
            $table->unsignedSmallInteger('etapa_1_anio')
                ->nullable();

            $table->decimal('etapa_1_monto_maximo', 15, 2)
                ->nullable();

            // Segunda etapa de financiamiento.
            $table->unsignedSmallInteger('etapa_2_anio')
                ->nullable();

            $table->decimal('etapa_2_monto_maximo', 15, 2)
                ->nullable();

            // Monto máximo total para este grupo.
            $table->decimal('monto_maximo_total', 15, 2);

            $table->string('moneda', 10)
                ->default('MXN');

            $table->timestamps();

            // Evitar registrar dos veces el mismo grupo
            // dentro de una convocatoria.
            $table->unique(
                ['convocatoria_id', 'grupo_clave'],
                'convocatoria_financiamiento_grupo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatoria_financiamientos');
    }
};
