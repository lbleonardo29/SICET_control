<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// La carta responsiva ya no se guarda como archivo en disco: se genera al
// vuelo bajo demanda a partir de los datos de la asignación. Esta columna
// dejó de usarse.
return new class extends Migration
{
    public function up()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn('carta_pdf');
        });

        Schema::table('asignaciones_moviles', function (Blueprint $table) {
            $table->dropColumn('carta_pdf');
        });
    }

    public function down()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->string('carta_pdf')->nullable();
        });

        Schema::table('asignaciones_moviles', function (Blueprint $table) {
            $table->string('carta_pdf')->nullable()->after('id');
        });
    }
};
