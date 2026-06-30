<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
        'area',
        'inconsistencias',
        'tipo',
        'user_id',
        'numero_empleado',  // AGREGAR ESTA LÍNEA
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'numero_empleado', 'id_emp');
    }
}