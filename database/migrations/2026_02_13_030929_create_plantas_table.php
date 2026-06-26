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
    Schema::create('plantas', function (Blueprint $table) {
        $table->id();
        $table->string('nombre')->unique(); // Jardín, Partidas, Sauces
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     * * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plantas');
    }
};
