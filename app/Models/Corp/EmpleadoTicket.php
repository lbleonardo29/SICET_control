<?php

namespace App\Models\Corp;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo del lado corporativo: tabla `tbl_empleados` de la base `tickets`.
 * Esquema VIEJO (id_emp manual, nombre+apellidos, email, activo 'S'/'N', id_planta).
 * Solo se usa para sincronización (lectura) y write-through (escritura).
 */
class EmpleadoTicket extends Model
{
    protected $connection = 'tickets';
    protected $table = 'tbl_empleados';
    protected $primaryKey = 'id_emp';

    // id_emp NO es autoincrement (se asigna manual = número de empleado).
    public $incrementing = false;
    protected $keyType = 'int';

    // tbl_empleados no tiene created_at/updated_at.
    public $timestamps = false;

    /**
     * Solo las columnas que SICET gestiona o necesita escribir.
     * Las columnas corporativas no gestionadas (contrasenia, id_rol, temporal,
     * politica, area) se omiten deliberadamente para no pisarlas en updates.
     */
    protected $fillable = [
        'id_emp',
        'nombre',
        'apellidos',
        'email',
        'activo',
        'id_planta',
    ];
}
