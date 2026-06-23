<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('asignaciones', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('asignaciones', 'token_confirmacion')) {
                $table->dropColumn('token_confirmacion');
            }
        });

        Schema::table('asignaciones_moviles', function (Blueprint $table) {
            if (Schema::hasColumn('asignaciones_moviles', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('asignaciones_moviles', 'token_confirmacion')) {
                $table->dropColumn('token_confirmacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'activo', 'rechazado'])->nullable()->after('estado_asignacion');
            $table->string('token_confirmacion')->nullable();
        });

        Schema::table('asignaciones_moviles', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'activo', 'rechazado'])->nullable()->after('estado_asignacion');
            $table->string('token_confirmacion')->nullable();
        });
    }
};
