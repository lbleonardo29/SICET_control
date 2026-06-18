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

    protected $table = 'tbl_empleados';
    protected $primaryKey = 'id_emp';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'activo',
        'id_rol',
        'area',
        'id_planta',
        'numero_empleado', 
    ];

    // 🔥 FORZAR ACTIVO A BOOLEAN
    protected $casts = [
        'activo' => 'boolean',
    ];

    /* =========================
       RELACIONES
    ========================== */

    public function user()
    {
        return $this->hasOne(User::class, 'empleado_id', 'id_emp');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'empleado_id', 'id_emp');
    }

    public function asignacionesMoviles()
    {
        return $this->hasMany(AsignacionMovil::class, 'empleado_id', 'id_emp');
    }

    /* =========================
       ACCESOR
    ========================== */

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }
}