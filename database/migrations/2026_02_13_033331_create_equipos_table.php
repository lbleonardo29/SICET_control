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
    Schema::create('equipos', function (Blueprint $table) {
        $table->id();

        $table->string('codigo_interno')->unique(); // Ej: SICET-0001
        $table->string('marca');
        $table->string('modelo');
        $table->string('numero_serie')->unique();

        $table->string('color')->nullable();
        $table->string('procesador')->nullable();
        $table->string('ram')->nullable();
        $table->string('ssd')->nullable();

        $table->boolean('cargador')->default(true);

        $table->date('fecha_adquisicion')->nullable();

        $table->foreignId('planta_id')
              ->constrained('plantas')
              ->onDelete('cascade');

        $table->enum('estado', [
            'Disponible',
            'Asignado',
            'Mantenimiento',
            'Baja'
        ])->default('Disponible');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     * * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipos');
    }
};
