<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoAndTokenToAsignaciones extends Migration
{
    public function up()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'activo', 'rechazado'])->default('pendiente');
            $table->string('token_confirmacion')->nullable();
        });
    }

    public function down()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn(['estado', 'token_confirmacion']);
        });
    }
}