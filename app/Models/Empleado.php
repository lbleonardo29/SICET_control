<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Empleado = proxy de SOLO LECTURA sobre la tabla corporativa `tbl_empleados`
 * (base `tickets`). La tabla local `empleados` de SICET fue eliminada: el
 * corporativo es el maestro y aquí solo se consulta.
 *
 * Llave estable: id_emp (= numero_empleado). Se exponen accesores
 * nombre_completo / correo / numero_empleado para mantener compatibilidad con
 * las vistas y controladores que usaban el esquema local anterior.
 */
class Empleado extends Model
{
    use HasFactory;

    protected $connection = 'tickets';
    protected $table = 'tbl_empleados';
    protected $primaryKey = 'id_emp';

    // id_emp se asigna manualmente (= número de empleado), no es autoincrement.
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    // Atributos calculados que las vistas/relaciones esperan.
    protected $appends = ['nombre_completo', 'correo', 'numero_empleado', 'es_activo'];

    /* =========================
       SOLO LECTURA
    ========================== */
    protected static function booted()
    {
        // Blindaje: el corporativo es de solo lectura desde SICET.
        foreach (['creating', 'updating', 'deleting', 'saving'] as $evento) {
            static::$evento(fn () => false);
        }
    }

    /* =========================
       ACCESORES DE COMPATIBILIDAD
    ========================== */
    public function getNombreCompletoAttribute(): string
    {
        return trim(($this->nombre ?? '') . ' ' . ($this->apellidos ?? ''));
    }

    public function getCorreoAttribute(): ?string
    {
        return $this->email;
    }

    public function getNumeroEmpleadoAttribute(): string
    {
        return (string) $this->id_emp;
    }

    public function getEsActivoAttribute(): bool
    {
        return in_array($this->activo, [1, '1', 'S', 's'], true);
    }

    /* =========================
       SCOPES
    ========================== */
    public function scopeActivos($query)
    {
        return $query->whereIn('activo', [1, '1', 'S', 's']);
    }

    public function scopeBuscar($query, ?string $termino)
    {
        if (!$termino) {
            return $query;
        }
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('apellidos', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%")
              ->orWhere('id_emp', 'like', "%{$termino}%");
        });
    }

    // NOTA IMPORTANTE: a propósito NO se definen relaciones (user, asignaciones,
    // asignacionesMoviles, planta...) hacia tablas de la BD local. Eloquent
    // propaga la conexión `tickets` del padre a la consulta hija, y el usuario
    // de `tickets` no tiene permiso sobre la BD local -> cualquier relación así
    // truena con "SELECT command denied" en cuanto se ejecuta como query real
    // (no basta con que "se vea bien" en un `with()`; cualquier ->metodo() la
    // dispara). La forma correcta de obtener estos datos:
    //   - Vínculo con User: consultar User::where('numero_empleado', ...) aparte
    //     y usar setRelation() para adjuntarlo (ver EmpleadoController::index()).
    //   - Asignaciones/asignacionesMoviles: consultar Asignacion/AsignacionMovil
    //     directamente con where('empleado_id', $empleado->id_emp) (ver
    //     AsignacionController::historialEmpleado(), AdminController::dashboard()).
    //   - Planta: consultar Planta::where('id_planta_corp', ...) aparte (ver
    //     EmpleadoController::index() y AsignacionController::vincularPlantas()).
}
