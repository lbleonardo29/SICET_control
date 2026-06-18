<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asignaciones', function (Blueprint $table) {

            // 1️⃣ Eliminar FK vieja
            $table->dropForeign('fk_asignaciones_empleado');

            // 2️⃣ Cambiar tipo de columna
            $table->integer('empleado_id')->change();

            // 3️⃣ Crear FK correcta
            $table->foreign('empleado_id')
                  ->references('id_emp')
                  ->on('tbl_empleados')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('asignaciones', function (Blueprint $table) {

            // Eliminar FK correcta
            $table->dropForeign(['empleado_id']);

            // Restaurar tipo anterior (bigint unsigned)
            $table->unsignedBigInteger('empleado_id')->change();

            // Crear FK vieja (si existía)
            $table->foreign('empleado_id')
                  ->references('id')
                  ->on('empleados')
                  ->onDelete('cascade');
        });
    }
};