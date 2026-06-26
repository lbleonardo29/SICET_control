<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlantaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * * @return void
     */
  public function run()
{
    \App\Models\Planta::create(['nombre' => 'Jardín']);
    \App\Models\Planta::create(['nombre' => 'Partidas']);
    \App\Models\Planta::create(['nombre' => 'Sauces']);
}

}
