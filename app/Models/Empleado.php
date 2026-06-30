<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Asignacion;
use App\Models\AsignacionMovil;

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

    /* =========================
       RELACIONES (clave: id_emp)
    ========================== */
    public function user()
    {
        return $this->hasOne(User::class, 'numero_empleado', 'id_emp');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'empleado_id', 'id_emp');
    }

    public function asignacionesMoviles()
    {
        return $this->hasMany(AsignacionMovil::class, 'empleado_id', 'id_emp');
    }

    // NOTA: no se define relación hacia tablas locales (users, plantas) porque
    // Eloquent propaga la conexión `tickets` del padre a la consulta hija, y el
    // usuario de `tickets` no tiene acceso a la BD local. Esos vínculos se
    // resuelven manualmente en los controladores (ver EmpleadoController/
    // AsignacionController) con setRelation usando la conexión por defecto.
}
