<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1) Permite user_id NULL en asignaciones (y asignaciones_moviles): un equipo
     *    puede asignarse a un empleado que aún no ha iniciado sesión en SICET.
     * 2) Backfill: vincula users.empleado_id con empleados.id por numero_empleado.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE asignaciones MODIFY user_id BIGINT UNSIGNED NULL');

        if (Schema::hasColumn('asignaciones_moviles', 'user_id')) {
            DB::statement('ALTER TABLE asignaciones_moviles MODIFY user_id BIGINT UNSIGNED NULL');
        }

        DB::statement('
            UPDATE users u
            JOIN empleados e ON e.numero_empleado = u.numero_empleado
            SET u.empleado_id = e.id
            WHERE u.empleado_id IS NULL
        ');
    }

    public function down(): void
    {
        // No se revierte el backfill. Las columnas se dejan nullable.
    }
};
