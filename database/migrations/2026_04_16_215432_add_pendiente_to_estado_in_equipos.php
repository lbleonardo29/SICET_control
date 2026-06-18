<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estado ENUM('Disponible', 'Asignado', 'Pendiente', 'En reparación', 'Baja') DEFAULT 'Disponible'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE equipos MODIFY COLUMN estado ENUM('Disponible', 'Asignado', 'En reparación', 'Baja') DEFAULT 'Disponible'");
    }
};