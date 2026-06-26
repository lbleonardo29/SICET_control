<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Firma electrónica del empleado (imagen PNG en base64) y fecha de firma,
     * para computadoras y móviles.
     */
    public function up(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->longText('firma')->nullable()->after('carta_pdf');
            $table->timestamp('fecha_firma')->nullable()->after('firma');
        });

        Schema::table('asignaciones_moviles', function (Blueprint $table) {
            $table->longText('firma')->nullable()->after('carta_pdf');
            $table->timestamp('fecha_firma')->nullable()->after('firma');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn(['firma', 'fecha_firma']);
        });
        Schema::table('asignaciones_moviles', function (Blueprint $table) {
            $table->dropColumn(['firma', 'fecha_firma']);
        });
    }
};
