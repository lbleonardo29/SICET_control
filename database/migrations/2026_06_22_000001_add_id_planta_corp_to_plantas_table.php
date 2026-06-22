<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columna puente para mapear de forma determinista cada planta local de SICET
     * con su contraparte en la base corporativa `tickets` (planta.id_planta).
     */
    public function up(): void
    {
        Schema::table('plantas', function (Blueprint $table) {
            if (!Schema::hasColumn('plantas', 'id_planta_corp')) {
                $table->unsignedInteger('id_planta_corp')->nullable()->index()->after('nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plantas', function (Blueprint $table) {
            if (Schema::hasColumn('plantas', 'id_planta_corp')) {
                $table->dropColumn('id_planta_corp');
            }
        });
    }
};
