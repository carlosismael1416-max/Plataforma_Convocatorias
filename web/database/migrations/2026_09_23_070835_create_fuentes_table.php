<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuentes', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 255);

            $table->text('url_base')->unique();

            $table->string('tipo_fuente', 30)
                ->default('WEB');

            $table->jsonb('selector_config')->nullable();

            $table->boolean('requiere_javascript')
                ->default(false);

            $table->boolean('activa')
                ->default(false);

            $table->unsignedInteger('frecuencia_scraping')
                ->nullable();

            $table->timestampTz('ultima_ejecucion')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuentes');
    }
};
