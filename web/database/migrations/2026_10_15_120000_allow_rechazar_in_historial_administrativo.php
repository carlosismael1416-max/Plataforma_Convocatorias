<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El directivo puede rechazar una convocatoria pendiente: queda DESCARTADA y
 * la decisión (con observaciones obligatorias) se registra en el historial.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE convocatoria_revisiones_administrativas DROP CONSTRAINT chk_revision_admin_accion_estado');

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_accion_estado
            CHECK (
                (accion = 'APROBAR' AND estado_nuevo = 'PUBLICADA')
                OR (accion = 'SOLICITAR_CORRECCIONES' AND estado_nuevo = 'REQUIERE_CORRECCIONES')
                OR (
                    accion = 'REENVIAR_REVISION'
                    AND estado_anterior = 'REQUIERE_CORRECCIONES'
                    AND estado_nuevo = 'PENDIENTE_REVISION'
                )
                OR (
                    accion = 'RECHAZAR'
                    AND estado_anterior = 'PENDIENTE_REVISION'
                    AND estado_nuevo = 'DESCARTADA'
                )
            )
        SQL);

        DB::statement('ALTER TABLE convocatoria_revisiones_administrativas DROP CONSTRAINT chk_revision_admin_observaciones');

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_observaciones
            CHECK (
                accion NOT IN ('SOLICITAR_CORRECCIONES', 'REENVIAR_REVISION', 'RECHAZAR')
                OR NULLIF(BTRIM(COALESCE(observaciones, '')), '') IS NOT NULL
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE convocatoria_revisiones_administrativas DROP CONSTRAINT chk_revision_admin_accion_estado');

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_accion_estado
            CHECK (
                (accion = 'APROBAR' AND estado_nuevo = 'PUBLICADA')
                OR (accion = 'SOLICITAR_CORRECCIONES' AND estado_nuevo = 'REQUIERE_CORRECCIONES')
                OR (
                    accion = 'REENVIAR_REVISION'
                    AND estado_anterior = 'REQUIERE_CORRECCIONES'
                    AND estado_nuevo = 'PENDIENTE_REVISION'
                )
            )
        SQL);

        DB::statement('ALTER TABLE convocatoria_revisiones_administrativas DROP CONSTRAINT chk_revision_admin_observaciones');

        DB::statement(<<<'SQL'
            ALTER TABLE convocatoria_revisiones_administrativas
            ADD CONSTRAINT chk_revision_admin_observaciones
            CHECK (
                accion NOT IN ('SOLICITAR_CORRECCIONES', 'REENVIAR_REVISION')
                OR NULLIF(BTRIM(COALESCE(observaciones, '')), '') IS NOT NULL
            )
        SQL);
    }
};
