<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Esta migración era para corregir una FK a tbl_empleados (tabla renombrada).
    // La tabla asignaciones ya referencia correctamente a empleados.id — no se requiere acción.

    public function up()
    {
        // no-op
    }

    public function down()
    {
        // no-op
    }
};