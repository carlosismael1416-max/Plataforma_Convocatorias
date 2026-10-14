<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'convocatorias',
            function (Blueprint $table) {
                $table->foreignId(
                    'convocatoria_padre_id'
                )
                    ->nullable()
                    ->after('id')
                    ->constrained('convocatorias')
                    ->nullOnDelete();

                $table->index(
                    [
                        'convocatoria_padre_id',
                        'estado',
                    ],
                    'idx_convocatorias_padre_estado'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'convocatorias',
            function (Blueprint $table) {
                $table->dropIndex(
                    'idx_convocatorias_padre_estado'
                );

                $table->dropConstrainedForeignId(
                    'convocatoria_padre_id'
                );
            }
        );
    }
};
