<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // La tabla 'empleados' ya incluye 'numero_empleado' desde su migración original.
    // Esta migración referenciaba 'tbl_empleados' (tabla renombrada) — no se requiere acción.

    public function up()
    {
        // no-op
    }

    public function down()
    {
        // no-op
    }
};