<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispositivos_moviles', function (Blueprint $table) {
            $table->string('codigo_interno')->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('dispositivos_moviles', function (Blueprint $table) {
            $table->dropColumn('codigo_interno');
        });
    }
};