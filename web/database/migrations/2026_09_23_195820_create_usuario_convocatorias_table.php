<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_convocatorias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->boolean('es_favorita')
                ->default(true);

            $table->string('estado_seguimiento', 40)
                ->default('GUARDADA');

            $table->string('prioridad', 20)
                ->default('MEDIA');

            $table->text('notas')
                ->nullable();

            $table->string('ultima_accion', 150)
                ->nullable();

            $table->timestampTz('fecha_ultima_accion')
                ->nullable();

            $table->timestampTz('fecha_agregado')
                ->useCurrent();

            $table->timestampTz('fecha_actualizacion')
                ->useCurrent();

            $table->unique(
                ['user_id', 'convocatoria_id'],
                'usuario_convocatoria_unique'
            );

            $table->index('user_id');
            $table->index('convocatoria_id');
            $table->index('es_favorita');
            $table->index('estado_seguimiento');
            $table->index('prioridad');
        });

        DB::statement("
            ALTER TABLE usuario_convocatorias
            ADD CONSTRAINT chk_usuario_convocatorias_estado
            CHECK (
                estado_seguimiento IN (
                    'GUARDADA',
                    'REVISANDO',
                    'PREPARANDO_PROPUESTA',
                    'ENVIADA',
                    'ACEPTADA',
                    'RECHAZADA',
                    'FINALIZADA'
                )
            )
        ");

        DB::statement("
            ALTER TABLE usuario_convocatorias
            ADD CONSTRAINT chk_usuario_convocatorias_prioridad
            CHECK (
                prioridad IN (
                    'BAJA',
                    'MEDIA',
                    'ALTA'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_convocatorias');
    }
};
