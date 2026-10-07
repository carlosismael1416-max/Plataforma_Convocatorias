<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable(
                'notificacion_preferencias'
            )
        ) {
            return;
        }

        Schema::create(
            'notificacion_preferencias',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->unique()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->boolean(
                    'convocatorias_revision'
                )->default(true);

                $table->boolean(
                    'fechas_proximas'
                )->default(true);

                $table->boolean(
                    'nuevas_propuestas'
                )->default(true);

                $table->boolean(
                    'avisos_sistema'
                )->default(false);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'notificacion_preferencias'
        );
    }
};
