<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellidos', 150)->nullable()->after('name');

            $table->foreignId('role_id')
                ->nullable()
                ->after('password')
                ->constrained('roles')
                ->restrictOnDelete();

            $table->foreignId('departamento_id')
                ->nullable()
                ->after('role_id')
                ->constrained('departamentos')
                ->nullOnDelete();

            $table->boolean('estado')
                ->default(true)
                ->after('departamento_id');

            $table->timestamp('ultimo_acceso')
                ->nullable()
                ->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['departamento_id']);

            $table->dropColumn([
                'apellidos',
                'role_id',
                'departamento_id',
                'estado',
                'ultimo_acceso',
            ]);
        });
    }
};
