<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_interno',
        'marca',
        'modelo',
        'numero_serie',
        'color',
        'procesador',
        'ram',
        'ssd',
        'cargador',
        'fecha_adquisicion',
        'planta_id',
        'estado',
        'observaciones',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($equipo) {
            // 🔢 Obtener último código registrado
            $ultimo = self::orderBy('id', 'desc')->first();

            if ($ultimo && $ultimo->codigo_interno) {
                $numero = intval(substr($ultimo->codigo_interno, -4)) + 1;
            } else {
                $numero = 1;
            }

            $equipo->codigo_interno = 'SICET-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

            // 🔒 Forzar estado por defecto
            if (!$equipo->estado) {
                $equipo->estado = 'Disponible';
            }
        });
    }

    public function planta()
    {
        return $this->belongsTo(Planta::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function asignacionActiva()
    {
        return $this->hasOne(Asignacion::class)
            ->whereNull('fecha_devolucion');
    }

    /**
     * Obtiene la última asignación del equipo (útil para mostrar estado pendiente)
     */
    public function ultimaAsignacion()
    {
        return $this->hasOne(Asignacion::class, 'equipo_id')
                    ->latest('fecha_asignacion');
    }
}