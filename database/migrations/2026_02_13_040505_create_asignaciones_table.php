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
    Schema::create('asignaciones', function (Blueprint $table) {
        $table->id();

        $table->foreignId('equipo_id')
              ->constrained('equipos')
              ->onDelete('cascade');

        $table->foreignId('empleado_id')
              ->constrained('empleados')
              ->onDelete('cascade');

        $table->date('fecha_asignacion');

        $table->date('fecha_devolucion')->nullable();

        $table->string('carta_pdf')->nullable();

        $table->foreignId('user_id')
              ->constrained('users')
              ->onDelete('cascade');

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('asignaciones');
}

};
