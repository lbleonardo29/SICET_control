<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            // Estado de la asignación: pendiente (en espera), aceptada, rechazada
            $table->enum('estado_asignacion', ['pendiente', 'aceptada', 'rechazada'])
                  ->default('pendiente')
                  ->after('fecha_devolucion');
            
            // Fecha en que el empleado respondió (aceptó o rechazó)
            $table->timestamp('fecha_respuesta')->nullable()->after('estado_asignacion');
        });
    }

    public function down()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn(['estado_asignacion', 'fecha_respuesta']);
        });
    }
};