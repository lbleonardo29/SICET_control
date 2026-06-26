<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Equipo;
use App\Models\Empleado;
use App\Models\User;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'equipo_id',
        'empleado_id',
        'fecha_asignacion',
        'fecha_devolucion',
        'carta_pdf',
        'firma',               // NUEVO: firma electrónica (PNG base64)
        'fecha_firma',         // NUEVO: cuándo firmó
        'user_id',
        'estado_asignacion',   // NUEVO: pendiente, aceptada, rechazada
        'fecha_respuesta',     // NUEVO: cuando el empleado respondió
        // 'estado',            // Opcional: mantener por compatibilidad (ya no se usa)
        // 'token_confirmacion', // Ya no se usa (se eliminó)
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_devolucion' => 'datetime',
        'fecha_respuesta' => 'datetime',  // NUEVO
        'fecha_firma' => 'datetime',      // NUEVO
    ];

    /* =========================
       RELACIONES
    ========================== */

    // Equipo asignado (computadora)
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id', 'id');
    }

    // Empleado (PK = id en tabla empleados)
    public function empleado()
    {
        return $this->belongsTo(
            Empleado::class,
            'empleado_id',
            'id'
        );
    }

    // Usuario que hizo la asignación
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /* =========================
       SCOPES (consultas comunes)
    ========================== */

    // Scope para asignaciones pendientes
    public function scopePendientes($query)
    {
        return $query->where('estado_asignacion', 'pendiente');
    }

    // Scope para asignaciones aceptadas
    public function scopeAceptadas($query)
    {
        return $query->where('estado_asignacion', 'aceptada');
    }

    // Scope para asignaciones rechazadas
    public function scopeRechazadas($query)
    {
        return $query->where('estado_asignacion', 'rechazada');
    }

    // Scope para asignaciones activas (aceptadas y sin devolución)
    public function scopeActivas($query)
    {
        return $query->where('estado_asignacion', 'aceptada')
                     ->whereNull('fecha_devolucion');
    }

    /* =========================
       MUTATORS (para mayúsculas)
    ========================== */

    // Si quieres que ciertos campos se guarden en mayúsculas
    // public function setObservacionesAttribute($value)
    // {
    // $this->attributes['observaciones'] = strtoupper($value);
    // }
}