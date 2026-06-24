<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El perfil 'seguridad' se reemplaza por 'rh' (Recursos Humanos).
     * RH solo visualiza asignaciones (solo lectura); la función de reportes
     * de entrada/salida queda fuera del sistema.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'seguridad')->update(['role' => 'rh']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'rh')->update(['role' => 'seguridad']);
    }
};
