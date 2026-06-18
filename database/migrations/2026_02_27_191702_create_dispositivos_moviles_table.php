<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
Schema::create('dispositivos_moviles', function (Blueprint $table) {
    $table->id();
    $table->string('marca');
    $table->string('modelo');
    $table->string('imei')->unique();
    $table->string('numero_sim')->nullable();
    $table->string('numero_telefono')->nullable();
    $table->text('caracteristicas')->nullable();
    $table->string('estado')->default('Disponible');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispositivos_moviles');
    }
};
