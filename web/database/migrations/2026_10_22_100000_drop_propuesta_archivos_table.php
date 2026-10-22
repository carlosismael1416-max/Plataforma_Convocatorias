<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * propuesta_archivos nunca se usó (solo id y fechas, sin datos): los
 * archivos de una propuesta viven en evidencias y cotizaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('propuesta_archivos');
    }

    public function down(): void
    {
        Schema::create('propuesta_archivos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
