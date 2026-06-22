<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'id_planta_corp', // enlace con planta.id_planta de la base corporativa `tickets`
    ];
}
