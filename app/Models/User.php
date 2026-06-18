<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Empleado;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo',
        'empleado_id',
        'primer_inicio',
        'numero_empleado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 🔗 Relación con empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'id_emp');
    }

    // Relación con asignaciones de equipos
    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'user_id');
    }

    // Relación con asignaciones de móviles
    public function asignacionesMoviles()
    {
        return $this->hasMany(AsignacionMovil::class, 'user_id');
    }

    /**
     * Generar una contraseña temporal aleatoria
     */
    public static function generarPasswordTemporal()
    {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
        return substr(str_shuffle($caracteres), 0, 10);
    }
}