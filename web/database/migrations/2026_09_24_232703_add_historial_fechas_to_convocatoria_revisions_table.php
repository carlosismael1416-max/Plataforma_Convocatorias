<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convocatoria_revisions', function (Blueprint $table) {

            // Convocatoria a la que pertenece la revisión.
            $table->foreignId('convocatoria_id')
                ->nullable()
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            // Ejemplo: FECHA_CIERRE.
            $table->string('tipo_revision', 50)
                ->nullable();

            // Fecha anterior y fecha publicada
            // en el documento o aviso correspondiente.
            $table->date('fecha_cierre_anterior')
                ->nullable();

            $table->date('fecha_cierre_nueva')
                ->nullable();

            $table->time('hora_cierre')
                ->nullable();

            $table->string('zona_horaria', 100)
                ->default('America/Mexico_City');

            // Procedencia: PDF, WEB u otra fuente.
            $table->string('fuente_tipo', 30)
                ->nullable();

            $table->text('fuente_url')
                ->nullable();

            $table->text('observaciones')
                ->nullable();

            $table->boolean('requiere_verificacion')
                ->default(true);

            // Momento en que nuestro sistema
            // registra la revisión, no necesariamente
            // cuando se publicó el aviso.
            $table->timestampTz('fecha_revision')
                ->useCurrent();

            $table->index([
                'convocatoria_id',
                'tipo_revision',
            ], 'convocatoria_revisions_tipo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('convocatoria_revisions', function (Blueprint $table) {

            $table->dropIndex(
                'convocatoria_revisions_tipo_idx'
            );

            $table->dropConstrainedForeignId(
                'convocatoria_id'
            );

            $table->dropColumn([
                'tipo_revision',
                'fecha_cierre_anterior',
                'fecha_cierre_nueva',
                'hora_cierre',
                'zona_horaria',
                'fuente_tipo',
                'fuente_url',
                'observaciones',
                'requiere_verificacion',
                'fecha_revision',
            ]);
        });
    }
};
