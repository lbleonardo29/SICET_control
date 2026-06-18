<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispositivoMovil extends Model
{
    use HasFactory;

    protected $table = 'dispositivos_moviles';

    protected $fillable = [
        'codigo_interno',
        'marca',
        'modelo',
        'imei',
        'numero_sim',
        'numero_telefono',
        'caracteristicas',
        'estado',
        'asignado'
    ];

    protected $casts = [
        'asignado' => 'boolean',
    ];

    /* =====================================================
       🔗 RELACIONES
    ===================================================== */

    // historial de asignaciones
    public function asignaciones()
    {
        return $this->hasMany(
            AsignacionMovil::class,
            'dispositivo_movil_id'
        );
    }

    // asignación activa
    public function asignacionActiva()
    {
        return $this->hasOne(
            AsignacionMovil::class,
            'dispositivo_movil_id'
        )->whereNull('fecha_devolucion');
    }

    // última asignación (para mostrar estado pendiente)
    public function ultimaAsignacion()
    {
        return $this->hasOne(
            AsignacionMovil::class,
            'dispositivo_movil_id'
        )->latest('fecha_asignacion');
    }
}