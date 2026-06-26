<?php

namespace App\Models\Corp;

use Illuminate\Database\Eloquent\Model;

/**
     * Modelo del lado corporativo: tabla `planta` (singular) de la base `tickets`.
 */
class PlantaTicket extends Model
{
    protected $connection = 'tickets';
    protected $table = 'planta';
    protected $primaryKey = 'id_planta';

    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'nombre',
    ];
}
