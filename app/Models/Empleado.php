<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Asignacion;
use App\Models\AsignacionMovil;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    protected $primaryKey = 'id';

    protected $fillable = [
        'numero_empleado',
        'nombre_completo',
        'correo',
        'activo',
        'planta_id',
    ];

    // FORZAR ACTIVO A BOOLEAN
    protected $casts = [
        'activo' => 'boolean',
    ];

    /* =========================
       RELACIONES
    ========================== */

    public function user()
    {
        return $this->hasOne(User::class, 'empleado_id', 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'empleado_id', 'id');
    }

    public function asignacionesMoviles()
    {
        return $this->hasMany(AsignacionMovil::class, 'empleado_id', 'id');
    }
}