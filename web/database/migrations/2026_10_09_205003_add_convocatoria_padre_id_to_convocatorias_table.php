<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * La columna puede haber sido creada previamente
         * durante el desarrollo de la FASE 3.
         */
        if (! Schema::hasColumn(
            'convocatorias',
            'convocatoria_padre_id'
        )) {
            Schema::table(
                'convocatorias',
                function (Blueprint $table): void {
                    $table->unsignedBigInteger(
                        'convocatoria_padre_id'
                    )->nullable();
                }
            );
        }

        /*
         * Verificar si YA existe una FK para
         * convocatoria_padre_id.
         */
        $existeForeignKey = DB::selectOne(
            <<<'SQL'
            SELECT EXISTS (
                SELECT 1
                FROM pg_constraint c
                JOIN pg_class t
                  ON t.oid = c.conrelid
                JOIN pg_namespace n
                  ON n.oid = t.relnamespace
                WHERE n.nspname = 'public'
                  AND t.relname = 'convocatorias'
                  AND c.contype = 'f'
                  AND pg_get_constraintdef(c.oid)
                      ILIKE '%convocatoria_padre_id%'
            ) AS existe
            SQL
        );

        if (! $existeForeignKey->existe) {
            Schema::table(
                'convocatorias',
                function (Blueprint $table): void {
                    $table->foreign(
                        'convocatoria_padre_id',
                        'convocatorias_convocatoria_padre_id_foreign'
                    )
                        ->references('id')
                        ->on('convocatorias')
                        ->nullOnDelete();
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * Eliminar la FK si existe.
         */
        DB::statement(
            <<<'SQL'
            ALTER TABLE convocatorias
            DROP CONSTRAINT IF EXISTS
            convocatorias_convocatoria_padre_id_foreign
            SQL
        );

        if (Schema::hasColumn(
            'convocatorias',
            'convocatoria_padre_id'
        )) {
            Schema::table(
                'convocatorias',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'convocatoria_padre_id'
                    );
                }
            );
        }
    }
};
