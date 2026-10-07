<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            DROP CONSTRAINT chk_revision_admin_accion_estado
        SQL);

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
                OR
                (
                    accion = 'REENVIAR_REVISION'
                    AND estado_anterior = 'REQUIERE_CORRECCIONES'
                    AND estado_nuevo = 'PENDIENTE_REVISION'
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            DROP CONSTRAINT chk_revision_admin_observaciones
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_observaciones
            CHECK (
                accion NOT IN (
                    'SOLICITAR_CORRECCIONES',
                    'REENVIAR_REVISION'
                )
                OR NULLIF(
                    BTRIM(COALESCE(observaciones, '')),
                    ''
                ) IS NOT NULL
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            DROP CONSTRAINT chk_revision_admin_accion_estado
        SQL);

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

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            DROP CONSTRAINT chk_revision_admin_observaciones
        SQL);

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
};
