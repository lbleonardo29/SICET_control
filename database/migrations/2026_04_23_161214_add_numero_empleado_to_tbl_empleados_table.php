<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tbl_empleados', function (Blueprint $table) {
            $table->string('numero_empleado', 50)->nullable()->after('id_emp');
        });
    }

    public function down()
    {
        Schema::table('tbl_empleados', function (Blueprint $table) {
            $table->dropColumn('numero_empleado');
        });
    }
};