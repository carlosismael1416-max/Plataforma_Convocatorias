<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organismos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 255)->unique();

            $table->text('descripcion')->nullable();

            $table->text('sitio_web')->nullable();

            $table->string('tipo_organismo', 30)
                ->default('OTRO');

            $table->string('pais', 100)->nullable();

            $table->boolean('estado')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organismos');
    }
};
