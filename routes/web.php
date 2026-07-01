<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DispositivoMovilController;
use App\Http\Controllers\AsignacionMovilController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PasswordResetController;

/* |--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


// ================= LOGIN =================
Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');


// ================= RUTAS AUTENTICADAS (todos los roles) =================
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Perfil
    Route::get('/perfil', [ProfileController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');
    Route::delete('/perfil/eliminar-foto', [ProfileController::class, 'eliminarFoto'])->name('perfil.eliminar.foto');

    // Cambio de contraseña (primer inicio)
    Route::get('/cambiar-password', [ProfileController::class, 'cambiarPasswordForm'])->name('cambiar.password.form');
    Route::post('/cambiar-password', [ProfileController::class, 'cambiarPassword'])->name('cambiar.password');

    // Aceptar / rechazar asignaciones (el usuario acepta su propia asignación)
    Route::put('/asignaciones/aceptar/{id}', [AsignacionController::class, 'aceptar'])->name('asignaciones.aceptar');
    Route::put('/asignaciones/rechazar/{id}', [AsignacionController::class, 'rechazar'])->name('asignaciones.rechazar');
    Route::put('/asignaciones/firmar/{id}', [AsignacionController::class, 'firmar'])->name('asignaciones.firmar');
    Route::put('/asignaciones-moviles/aceptar/{id}', [AsignacionMovilController::class, 'aceptar'])->name('asignaciones.moviles.aceptar');
    Route::put('/asignaciones-moviles/rechazar/{id}', [AsignacionMovilController::class, 'rechazar'])->name('asignaciones.moviles.rechazar');
    Route::put('/asignaciones-moviles/firmar/{id}', [AsignacionMovilController::class, 'firmar'])->name('asignaciones.moviles.firmar');

    // Descargar carta responsiva (admin o el empleado dueño — verificado en el controlador)
    Route::get('/asignaciones/descargar/{id}', [AsignacionController::class, 'descargar'])->name('asignaciones.descargar');
    Route::get('/asignaciones-moviles/{id}/descargar', [AsignacionMovilController::class, 'descargar'])->name('asignaciones.moviles.descargar');

    // Notificaciones (campana) — todos los roles
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/leer-todas', [NotificacionController::class, 'leerTodas'])->name('notificaciones.leerTodas');
    Route::get('/notificaciones/{id}/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');

});


// ================= ASIGNACIONES - SOLO LECTURA (admin + rh) =================
// RH (Recursos Humanos) solo visualiza las asignaciones: empleado, equipo y estado.
Route::middleware(['auth', 'role:admin,rh'])->group(function () {
    Route::get('/asignaciones/dashboard', [AsignacionController::class, 'dashboard'])->name('asignaciones.dashboard');
    Route::get('/asignaciones-moviles', [AsignacionMovilController::class, 'dashboard'])->name('asignaciones.moviles.dashboard');
});


// ================= SOLO ADMIN =================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Asignaciones computadoras — gestión (dashboard solo-lectura está en grupo admin+rh)
    Route::get('/asignaciones', [AsignacionController::class, 'index'])->name('asignaciones.index');
    Route::get('/asignaciones/historial/empleado/{empleado_id}', [AsignacionController::class, 'historialEmpleado'])->name('asignaciones.historial.empleado');
    // (No existe "asignaciones.historial.equipo": sería redundante con
    // equipos.historial, que ya cubre "ver qué empleados ha tenido un equipo".)
    Route::get('/asignaciones/create/{equipo}', [AsignacionController::class, 'create'])->name('asignaciones.create');
    Route::post('/asignaciones', [AsignacionController::class, 'store'])->name('asignaciones.store');
    Route::put('/asignaciones/devolver/{id}', [AsignacionController::class, 'devolver'])->name('asignaciones.devolver');
    Route::delete('/asignaciones/{id}', [AsignacionController::class, 'destroy'])->name('asignaciones.destroy');

    // Catálogo UNIFICADO(NUEVO)
    Route::get('/catalogo', [EquipoController::class, 'catalogo'])->name('equipos.catalogo');

    // Equipos (computadoras)
    Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
    Route::get('/equipos/create', [EquipoController::class, 'create'])->name('equipos.create');
    Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
    Route::get('/equipos/disponibles', [EquipoController::class, 'disponiblesView'])->name('equipos.disponibles');
    Route::get('/equipos/{equipo}/historial', [EquipoController::class, 'historial'])->name('equipos.historial');
    Route::get('/equipos/{equipo}/edit', [EquipoController::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');
    Route::get('/equipos/{equipo}', [EquipoController::class, 'show'])->name('equipos.show');
    Route::get('/equipos/{equipo}/carta-responsiva', [AsignacionController::class, 'cartaResponsiva'])->name('equipos.carta');
    Route::put('/equipos/{id}/baja', [EquipoController::class, 'darDeBaja'])->name('equipos.baja');

    // Empleados — DIRECTORIO DE SOLO LECTURA (los datos viven en tickets.tbl_empleados)
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::post('/empleados/{id_emp}/asignar-rol', [EmpleadoController::class, 'asignarRol'])->name('empleados.asignarRol');

    // Dispositivos móviles
    Route::get('/moviles', [DispositivoMovilController::class, 'index'])->name('moviles.index');
    Route::get('/moviles/create', [DispositivoMovilController::class, 'create'])->name('moviles.create');
    Route::post('/moviles', [DispositivoMovilController::class, 'store'])->name('moviles.store');
    Route::get('/moviles/disponibles', [DispositivoMovilController::class, 'disponibles'])->name('moviles.disponibles');
    Route::get('/moviles/{movil}/historial', [DispositivoMovilController::class, 'historial'])->name('moviles.historial');
    Route::get('/moviles/{movil}/edit', [DispositivoMovilController::class, 'edit'])->name('moviles.edit');
    Route::put('/moviles/{movil}', [DispositivoMovilController::class, 'update'])->name('moviles.update');
    Route::delete('/moviles/{movil}', [DispositivoMovilController::class, 'destroy'])->name('moviles.destroy');
    Route::get('/moviles/{movil}', [DispositivoMovilController::class, 'show'])->name('moviles.show');
    Route::put('/moviles/{id}/baja', [DispositivoMovilController::class, 'darDeBaja'])->name('moviles.baja');

    // Asignaciones de móviles — gestión (dashboard solo-lectura está en grupo admin+rh)
    Route::get('/asignaciones-moviles/nueva/{id}', [DispositivoMovilController::class, 'createAsignacion'])->name('asignaciones.moviles.create');
    Route::post('/asignaciones-moviles', [AsignacionMovilController::class, 'store'])->name('asignaciones.moviles.store');
    Route::put('/asignaciones-moviles/{id}/devolver', [AsignacionMovilController::class, 'devolver'])->name('moviles.devolver');
    Route::get('/asignaciones-moviles/{id}/responsiva', [AsignacionMovilController::class, 'responsiva'])->name('asignaciones.moviles.responsiva');
    Route::delete('/asignaciones-moviles/{id}', [AsignacionMovilController::class, 'destroy'])->name('asignaciones.moviles.destroy');

    // Gestión de usuarios
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios.index');
    Route::put('/usuarios/{id}/rol', [AdminController::class, 'actualizarRol'])->name('usuarios.rol');

});


// ================= API (autenticado) =================
Route::middleware('auth')->group(function () {

    Route::get('/api/empleado/{id}/computadoras', function ($id) {
        $asignaciones = App\Models\Asignacion::where('empleado_id', $id)
            ->where('estado_asignacion', 'aceptada')
            ->whereNull('fecha_devolucion')
            ->with('equipo')
            ->get();

        return response()->json($asignaciones->map(function ($a) {
            return [
                'codigo_interno' => $a->equipo->codigo_interno,
                'marca'          => $a->equipo->marca,
                'modelo'         => $a->equipo->modelo,
            ];
        }));
    })->name('api.empleado.computadoras');

    Route::get('/api/empleados/search', function (Request $request) {
        try {
            $q = $request->get('q');
            if (strlen($q) < 2) {
                return response()->json([]);
            }
            $empleados = App\Models\Empleado::activos()
                ->buscar($q)
                ->limit(20)
                ->get()
                ->map(fn ($e) => [
                    'id'              => $e->id_emp,
                    'nombre_completo' => $e->nombre_completo,
                    'numero_empleado' => $e->numero_empleado,
                    'correo'          => $e->correo,
                ]);
            return response()->json($empleados);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('api.empleados.search');

});


// ================= RECUPERACIÓN DE CONTRASEÑA =================
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
