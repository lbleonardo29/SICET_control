<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionMovil extends Model
{
    protected $table = 'asignaciones_moviles';

    protected $fillable = [
        'dispositivo_movil_id',
        'empleado_id',
        'user_id',
        'fecha_asignacion',
        'fecha_devolucion',
        'carta_pdf',
        'estado_asignacion',   // NUEVO: pendiente, aceptada, rechazada
        'fecha_respuesta',     // NUEVO: cuando el empleado respondió
        // 'estado',            // Opcional: mantener por compatibilidad (ya no se usa)
        // 'token_confirmacion', // Ya no se usa (se eliminó)
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_devolucion' => 'datetime',
        'fecha_respuesta' => 'datetime',  // NUEVO
    ];

    /* =====================================================
       RELACIONES
    ===================================================== */

    public function dispositivo()
    {
        return $this->belongsTo(
            DispositivoMovil::class,
            'dispositivo_movil_id'
        );
    }

    public function empleado()
    {
        return $this->belongsTo(
            Empleado::class,
            'empleado_id',
            'id_emp'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =====================================================
       SCOPES (consultas comunes)
    ===================================================== */

    // 🔹 Scope para asignaciones pendientes
    public function scopePendientes($query)
    {
        return $query->where('estado_asignacion', 'pendiente');
    }

    // 🔹 Scope para asignaciones aceptadas
    public function scopeAceptadas($query)
    {
        return $query->where('estado_asignacion', 'aceptada');
    }

    // 🔹 Scope para asignaciones rechazadas
    public function scopeRechazadas($query)
    {
        return $query->where('estado_asignacion', 'rechazada');
    }

    // 🔹 Scope para asignaciones activas (aceptadas y sin devolución)
    public function scopeActivas($query)
    {
        return $query->where('estado_asignacion', 'aceptada')
                     ->whereNull('fecha_devolucion');
    }
}