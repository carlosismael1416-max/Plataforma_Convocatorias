<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_eventos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->nullable()
                ->constrained('convocatorias')
                ->nullOnDelete();

            $table->foreignId('ejecucion_scraping_id')
                ->nullable()
                ->constrained('ejecuciones_scraping')
                ->nullOnDelete();

            $table->string('tipo_evento', 50);

            $table->text('endpoint');

            $table->string('metodo_http', 10)
                ->default('POST');

            $table->jsonb('payload')
                ->nullable();

            $table->unsignedSmallInteger('codigo_respuesta')
                ->nullable();

            $table->jsonb('respuesta')
                ->nullable();

            $table->string('estado', 20)
                ->default('PENDIENTE');

            $table->unsignedInteger('intentos')
                ->default(0);

            $table->timestampTz('fecha_envio')
                ->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('tipo_evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_eventos');
    }
};
