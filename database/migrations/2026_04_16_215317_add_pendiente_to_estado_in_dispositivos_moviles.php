<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modificar la columna ENUM para agregar 'Pendiente'
        DB::statement("ALTER TABLE dispositivos_moviles MODIFY COLUMN estado ENUM('Disponible', 'Asignado', 'Pendiente', 'En reparación', 'Baja') DEFAULT 'Disponible'");
    }

    public function down()
    {
        // Revertir al estado anterior
        DB::statement("ALTER TABLE dispositivos_moviles MODIFY COLUMN estado ENUM('Disponible', 'Asignado', 'En reparación', 'Baja') DEFAULT 'Disponible'");
    }
};