<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La tabla local `empleados` deja de ser el maestro: el empleado ahora vive en
 * tickets.tbl_empleados (llave id_emp = numero_empleado). Las asignaciones
 * guardaban en `empleado_id` el viejo id autoincrement local (huérfano) y tenían
 * una FK hacia empleados(id).
 *
 * 1) Soltamos la FK empleado_id -> empleados(id) en ambas tablas (ya no aplica:
 *    empleado_id pasará a contener el id_emp corporativo).
 * 2) Recuperamos el vínculo desde el usuario: users.numero_empleado = id_emp.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropEmpleadoFk('asignaciones');
        $this->dropEmpleadoFk('asignaciones_moviles');

        DB::statement("
            UPDATE asignaciones a
            INNER JOIN users u ON a.user_id = u.id
            SET a.empleado_id = u.numero_empleado
            WHERE u.numero_empleado IS NOT NULL AND u.numero_empleado <> ''
        ");

        DB::statement("
            UPDATE asignaciones_moviles a
            INNER JOIN users u ON a.user_id = u.id
            SET a.empleado_id = u.numero_empleado
            WHERE u.numero_empleado IS NOT NULL AND u.numero_empleado <> ''
        ");
    }

    public function down(): void
    {
        // No reversible: el id local original ya no existe.
    }

    /**
     * Suelta cualquier FK sobre la columna empleado_id de la tabla dada.
     */
    private function dropEmpleadoFk(string $tabla): void
    {
        $db = DB::getDatabaseName();

        $fks = DB::select(
            "SELECT CONSTRAINT_NAME
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND COLUMN_NAME = 'empleado_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$db, $tabla]
        );

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$tabla}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
    }
};
