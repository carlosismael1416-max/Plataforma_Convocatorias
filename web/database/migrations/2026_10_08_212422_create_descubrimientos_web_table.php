<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descubrimientos_web', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identificación del descubrimiento
            |--------------------------------------------------------------------------
            */

            $table->string('titulo', 1000)->nullable();

            $table->text('url_original');

            $table->text('url_normalizada');

            /*
             * SHA-256 de url_normalizada.
             *
             * Se usa como clave única porque una URL puede ser
             * demasiado grande para indexarla directamente.
             */
            $table->char('url_hash', 64)->unique();

            $table->string('dominio', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Cómo fue encontrado
            |--------------------------------------------------------------------------
            */

            $table->string(
                'metodo_descubrimiento',
                100
            )->nullable();

            $table->text(
                'pagina_origen'
            )->nullable();

            $table->string(
                'tipo',
                60
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Clasificación técnica
            |--------------------------------------------------------------------------
            */

            $table->integer(
                'puntaje'
            )->default(0);

            $table->string(
                'relevancia_tematica',
                30
            )->nullable();

            $table->string(
                'estado_temporal',
                30
            )->nullable();

            $table->jsonb(
                'anios_detectados'
            )->nullable();

            $table->string(
                'origen_anio',
                30
            )->nullable();

            $table->jsonb(
                'razones'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Clasificación para el objetivo ITSVA
            |--------------------------------------------------------------------------
            */

            $table->string(
                'categoria_objetivo',
                100
            )->nullable();

            $table->string(
                'decision_objetivo',
                30
            )->nullable();

            $table->integer(
                'puntaje_objetivo'
            )->default(0);

            $table->jsonb(
                'razones_objetivo'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado interno del descubrimiento
            |--------------------------------------------------------------------------
            |
            | NUEVO
            | REVISION
            | DESCARTADO
            | EN_PROCESO
            | PROCESADO
            | ERROR
            |
            */

            $table->string(
                'estado',
                30
            )->default('NUEVO');

            $table->string(
                'estado_fuente',
                100
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Evidencia del descubrimiento
            |--------------------------------------------------------------------------
            */

            $table->jsonb(
                'evidencia'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'fuente_id'
            )
                ->nullable()
                ->constrained('fuentes')
                ->nullOnDelete();

            /*
             * Solo se asignará cuando el descubrimiento
             * ya haya generado una convocatoria real.
             */
            $table->foreignId(
                'convocatoria_id'
            )
                ->nullable()
                ->constrained('convocatorias')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Seguimiento
            |--------------------------------------------------------------------------
            */

            $table->timestampTz(
                'descubierto_en'
            )->useCurrent();

            $table->timestampTz(
                'ultimo_visto_en'
            )->useCurrent();

            $table->timestampTz(
                'procesado_en'
            )->nullable();

            $table->text(
                'error_ultimo'
            )->nullable();

            $table->timestampsTz();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index(
                'estado',
                'desc_web_estado_idx'
            );

            $table->index(
                'decision_objetivo',
                'desc_web_decision_idx'
            );

            $table->index(
                'estado_temporal',
                'desc_web_temporal_idx'
            );

            $table->index(
                'relevancia_tematica',
                'desc_web_relevancia_idx'
            );

            $table->index(
                'dominio',
                'desc_web_dominio_idx'
            );

            $table->index(
                [
                    'estado',
                    'decision_objetivo',
                ],
                'desc_web_estado_decision_idx'
            );

            $table->index(
                [
                    'decision_objetivo',
                    'estado_temporal',
                ],
                'desc_web_decision_temporal_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'descubrimientos_web'
        );
    }
};
