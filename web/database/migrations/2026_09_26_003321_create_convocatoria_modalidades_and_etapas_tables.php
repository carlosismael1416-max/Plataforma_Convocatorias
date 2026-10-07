
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatoria_modalidades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->string('clave', 80);
            $table->string('nombre', 160);
            $table->string('moneda', 3);

            $table->decimal('monto_maximo_total', 18, 2);

            $table->string('estado_documental', 40)
                ->default('PENDIENTE_REVISION');

            // Referencias a los PDF:
            // URL, nombre, página y fragmento original.
            $table->jsonb('fuentes_documentales')
                ->nullable();

            $table->timestamps();

            // No duplicar una modalidad dentro
            // de la misma convocatoria.
            $table->unique(
                ['convocatoria_id', 'clave'],
                'uq_convocatoria_modalidad_clave'
            );
        });

        Schema::create('convocatoria_modalidad_etapas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('modalidad_id')
                ->constrained('convocatoria_modalidades')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('numero');
            $table->unsignedSmallInteger('anio');

            $table->decimal('monto_maximo', 18, 2);

            $table->timestamps();

            // Cada modalidad solo puede tener
            // una etapa con el mismo número.
            $table->unique(
                ['modalidad_id', 'numero'],
                'uq_modalidad_numero_etapa'
            );
        });

        DB::statement(
            'ALTER TABLE convocatoria_modalidades
             ADD CONSTRAINT chk_modalidad_monto
             CHECK (monto_maximo_total >= 0)'
        );

        DB::statement(
            'ALTER TABLE convocatoria_modalidad_etapas
             ADD CONSTRAINT chk_etapa_monto_numero
             CHECK (monto_maximo >= 0 AND numero >= 1)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'convocatoria_modalidad_etapas'
        );

        Schema::dropIfExists(
            'convocatoria_modalidades'
        );
    }
};
