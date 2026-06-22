<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asignaciones_moviles', function (Blueprint $table) {
    $table->id();

    // Dispositivo móvil
    $table->foreignId('dispositivo_movil_id')
          ->constrained('dispositivos_moviles')
          ->onDelete('cascade');

    $table->foreignId('empleado_id')
          ->constrained('empleados')
          ->onDelete('cascade');

    // Usuario que asigna
    $table->foreignId('user_id')
          ->constrained('users')
          ->onDelete('cascade');

    $table->timestamp('fecha_asignacion');
    $table->timestamp('fecha_devolucion')->nullable();

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_moviles');
    }
};