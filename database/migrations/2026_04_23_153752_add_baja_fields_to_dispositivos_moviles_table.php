<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dispositivos_moviles', function (Blueprint $table) {
            $table->timestamp('fecha_baja')->nullable();
            $table->text('motivo_baja')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispositivos_moviles', function (Blueprint $table) {
            $table->dropColumn(['fecha_baja', 'motivo_baja']);
        });
    }
};