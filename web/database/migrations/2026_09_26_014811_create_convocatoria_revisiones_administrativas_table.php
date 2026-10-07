
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'convocatoria_revisiones_administrativas',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('convocatoria_id')
                    ->constrained('convocatorias')
                    ->restrictOnDelete();

                $table->foreignId('revisor_id')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string('accion', 40);

                $table->string('estado_anterior', 50);
                $table->string('estado_nuevo', 50);

                $table->text('observaciones')->nullable();

                $table->timestampTz('fecha_decision')
                    ->useCurrent();

                $table->index(
                    ['convocatoria_id', 'fecha_decision'],
                    'idx_revision_admin_convocatoria_fecha'
                );
            }
        );

        // Las decisiones admitidas inicialmente son
        // aprobar o solicitar correcciones.
        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_accion_estado
            CHECK (
                (
                    accion = 'APROBAR'
                    AND estado_nuevo = 'PUBLICADA'
                )
                OR
                (
                    accion = 'SOLICITAR_CORRECCIONES'
                    AND estado_nuevo = 'REQUIERE_CORRECCIONES'
                )
            )
        SQL);

        // No se pueden solicitar correcciones
        // sin explicar qué necesita modificarse.
        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_observaciones
            CHECK (
                accion <> 'SOLICITAR_CORRECCIONES'
                OR NULLIF(
                    BTRIM(COALESCE(observaciones, '')),
                    ''
                ) IS NOT NULL
            )
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'convocatoria_revisiones_administrativas'
        );
    }
};
