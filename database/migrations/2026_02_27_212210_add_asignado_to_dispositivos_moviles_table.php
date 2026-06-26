<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * * @return void
     */
public function up()
{
    Schema::table('dispositivos_moviles', function (Blueprint $table) {
        $table->boolean('asignado')->default(false)->after('estado');
    });
}

public function down()
{
    Schema::table('dispositivos_moviles', function (Blueprint $table) {
        $table->dropColumn('asignado');
    });
}

    /**
     * Reverse the migrations.
     * * @return void
     */

};
