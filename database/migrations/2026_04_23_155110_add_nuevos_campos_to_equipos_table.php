<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('nombre_equipo')->nullable()->after('id');
            $table->string('tipo_almacenamiento')->nullable()->after('ssd');
            $table->string('capacidad_almacenamiento')->nullable()->after('tipo_almacenamiento');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['nombre_equipo', 'tipo_almacenamiento', 'capacidad_almacenamiento']);
        });
    }
};