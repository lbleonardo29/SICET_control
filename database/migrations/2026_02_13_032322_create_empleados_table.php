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
    Schema::create('empleados', function (Blueprint $table) {
        $table->id();

        $table->string('numero_empleado')->unique(); // Lo captura el admin
        $table->string('nombre_completo');
        $table->string('correo')->nullable(); // Para enviar carta responsiva

        $table->foreignId('planta_id')
              ->constrained('plantas')
              ->onDelete('cascade');

        $table->boolean('activo')->default(true);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     * * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empleados');
    }
};
