<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('descubrimientos_web', function (Blueprint $table) {
            $table->unsignedSmallInteger('evaluacion_score')
                ->default(0);

            $table->jsonb('evaluacion_detalle')
                ->nullable();

            $table->timestampTz('evaluado_en')
                ->nullable();

            $table->unsignedInteger('intentos_procesamiento')
                ->default(0);

            $table->text('ultima_salida_extractor')
                ->nullable();
        });

        Schema::table('descubrimientos_web', function (Blueprint $table) {
            $table->index(
                ['estado', 'evaluacion_score'],
                'desc_web_estado_score_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('descubrimientos_web', function (Blueprint $table) {
            $table->dropIndex(
                'desc_web_estado_score_idx'
            );

            $table->dropColumn([
                'evaluacion_score',
                'evaluacion_detalle',
                'evaluado_en',
                'intentos_procesamiento',
                'ultima_salida_extractor',
            ]);
        });
    }
};
