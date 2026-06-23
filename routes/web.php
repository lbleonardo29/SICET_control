<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;  // 👈 Agrega esta línea (para DB en las rutas API)
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DispositivoMovilController;
use App\Http\Controllers\AsignacionMovilController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PasswordResetController; // 👈 MOVER AQUÍ (al inicio con los demás controllers)

/*
|--------------------------------------------------------------------------
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


// ================= RUTAS CON LOGIN =================
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // ================= PERFIL =================
    Route::get('/perfil', [ProfileController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('perfil.update');

    // ================= CAMBIO DE CONTRASEÑA (PRIMER INICIO) =================
    Route::get('/cambiar-password', [ProfileController::class, 'cambiarPasswordForm'])->name('cambiar.password.form');
    Route::post('/cambiar-password', [ProfileController::class, 'cambiarPassword'])->name('cambiar.password');

    // ================= ASIGNACIONES COMPUTADORAS =================
    Route::get('/asignaciones/dashboard', [AsignacionController::class, 'dashboard'])->name('asignaciones.dashboard');
    Route::get('/asignaciones', [AsignacionController::class, 'index'])->name('asignaciones.index');

    Route::get('/asignaciones/historial/empleado/{empleado_id}',
        [AsignacionController::class, 'historialEmpleado']
    )->name('asignaciones.historial.empleado');

    Route::get('/asignaciones/historial/equipo/{equipo_id}',
        [AsignacionController::class, 'historialEquipo']
    )->name('asignaciones.historial.equipo');

    // ================= NUEVAS RUTAS PARA ACEPTAR/RECHAZAR (desde el sistema) =================
    Route::put('/asignaciones/aceptar/{id}', [AsignacionController::class, 'aceptar'])->name('asignaciones.aceptar');
    Route::put('/asignaciones/rechazar/{id}', [AsignacionController::class, 'rechazar'])->name('asignaciones.rechazar');

    // ================= EMPLEADOS =================
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');

    // ================= MOVILES =================
    Route::get('/moviles', [DispositivoMovilController::class, 'index'])->name('moviles.index');
    Route::get('/moviles/disponibles', [DispositivoMovilController::class, 'disponibles'])->name('moviles.disponibles');
    Route::get('/moviles/{movil}/historial', [DispositivoMovilController::class, 'historial'])->name('moviles.historial');

    // ================= ASIGNACIONES MOVILES =================
    Route::get('/asignaciones-moviles', [AsignacionMovilController::class, 'dashboard'])->name('asignaciones.moviles.dashboard');

    // ================= NUEVAS RUTAS PARA ACEPTAR/RECHAZAR MÓVILES (desde el sistema) =================
    Route::put('/asignaciones-moviles/aceptar/{id}', [AsignacionMovilController::class, 'aceptar'])->name('asignaciones.moviles.aceptar');
    Route::put('/asignaciones-moviles/rechazar/{id}', [AsignacionMovilController::class, 'rechazar'])->name('asignaciones.moviles.rechazar');

});


// ================= SOLO ADMIN (CRUDs y gestión) =================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // ================= EQUIPOS CRUD =================
    Route::get('/equipos/create', [EquipoController::class, 'create'])->name('equipos.create');
    Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
    Route::get('/equipos/disponibles', [EquipoController::class, 'disponiblesView'])->name('equipos.disponibles');
    Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
    Route::get('/equipos/{equipo}/historial', [EquipoController::class, 'historial'])->name('equipos.historial');
    Route::get('/equipos/{equipo}', [EquipoController::class, 'show'])->name('equipos.show');
    Route::get('/equipos/{equipo}/edit', [EquipoController::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');

    // ================= ASIGNACIONES COMPUTADORAS =================
    Route::get('/asignaciones/create/{equipo}', [AsignacionController::class, 'create'])->name('asignaciones.create');
    Route::post('/asignaciones', [AsignacionController::class, 'store'])->name('asignaciones.store');
    Route::put('/asignaciones/devolver/{id}', [AsignacionController::class, 'devolver'])->name('asignaciones.devolver');
    Route::get('/asignaciones/descargar/{id}', [AsignacionController::class, 'descargar'])->name('asignaciones.descargar');
    Route::delete('/asignaciones/{id}', [AsignacionController::class, 'destroy'])->name('asignaciones.destroy');
    Route::get('/equipos/{equipo}/carta-responsiva', [AsignacionController::class, 'cartaResponsiva'])->name('equipos.carta');

    // ================= EMPLEADOS CRUD =================
    Route::get('/empleados/create', [EmpleadoController::class, 'create'])->name('empleados.create');
    Route::post('/empleados', [EmpleadoController::class, 'store'])->name('empleados.store');
    Route::get('/empleados/{empleado}/edit', [EmpleadoController::class, 'edit'])->name('empleados.edit');
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
    Route::put('/empleados/{id}/toggle', [EmpleadoController::class, 'toggle'])->name('empleados.toggle');
    Route::delete('/empleados/{empleado}', [EmpleadoController::class, 'destroy'])->name('empleados.destroy');

    // ================= MOVILES CRUD =================
    Route::get('/moviles/create', [DispositivoMovilController::class, 'create'])->name('moviles.create');
    Route::post('/moviles', [DispositivoMovilController::class, 'store'])->name('moviles.store');
    Route::get('/moviles/{movil}/edit', [DispositivoMovilController::class, 'edit'])->name('moviles.edit');
    Route::put('/moviles/{movil}', [DispositivoMovilController::class, 'update'])->name('moviles.update');
    Route::delete('/moviles/{movil}', [DispositivoMovilController::class, 'destroy'])->name('moviles.destroy');
    Route::get('/moviles/{movil}', [DispositivoMovilController::class, 'show'])->name('moviles.show');

    // ================= ASIGNACIONES MOVILES =================
    Route::get('/asignaciones-moviles/nueva/{id}', [DispositivoMovilController::class, 'createAsignacion'])->name('asignaciones.moviles.create');
    Route::post('/asignaciones-moviles', [AsignacionMovilController::class, 'store'])->name('asignaciones.moviles.store');
    Route::put('/asignaciones-moviles/{id}/devolver', [AsignacionMovilController::class, 'devolver'])->name('moviles.devolver');
    Route::get('/asignaciones-moviles/{id}/responsiva', [AsignacionMovilController::class, 'responsiva'])->name('asignaciones.moviles.responsiva');
    Route::get('/asignaciones-moviles/{id}/descargar', [AsignacionMovilController::class, 'descargar'])->name('asignaciones.moviles.descargar');
    Route::delete('/asignaciones-moviles/{id}', [AsignacionMovilController::class, 'destroy'])->name('asignaciones.moviles.destroy');

    // ================= RUTAS PARA ADMIN (VER REPORTES) =================
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

});


// ================= RUTAS PARA SEGURIDAD (FUERA DEL GRUPO ADMIN) =================
Route::middleware(['auth', 'role:seguridad'])->group(function () {
    Route::get('/reportes/create', [ReporteController::class, 'create'])->name('reportes.create');
    Route::post('/reportes', [ReporteController::class, 'store'])->name('reportes.store');
});


// ================= RUTAS DE PERFIL =================
Route::delete('/perfil/eliminar-foto', [ProfileController::class, 'eliminarFoto'])->name('perfil.eliminar.foto');


// ================= API RUTAS =================
Route::get('/api/empleado/{id}/computadoras', function($id) {
    $asignaciones = App\Models\Asignacion::where('empleado_id', $id)
        ->where('estado_asignacion', 'aceptada')
        ->whereNull('fecha_devolucion')
        ->with('equipo')
        ->get();
    
    return response()->json($asignaciones->map(function($a) {
        return [
            'codigo_interno' => $a->equipo->codigo_interno,
            'marca' => $a->equipo->marca,
            'modelo' => $a->equipo->modelo,
        ];
    }));
})->name('api.empleado.computadoras');

// ================= API PARA BÚSQUEDA DE EMPLEADOS =================
Route::get('/api/empleados/search', function(Request $request) {
    try {
        $q = $request->get('q');
        
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        
        $empleados = DB::table('empleados')
            ->where('activo', 1)
            ->where(function($query) use ($q) {
                $query->where('nombre_completo', 'like', "%{$q}%")
                      ->orWhere('correo', 'like', "%{$q}%")
                      ->orWhere('numero_empleado', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get([
                'id',
                'nombre_completo',
                'numero_empleado',
                'correo',
            ]);
        
        return response()->json($empleados);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->name('api.empleados.search');


// ================= RECUPERACIÓN DE CONTRASEÑA =================
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::put('/equipos/{id}/baja', [EquipoController::class, 'darDeBaja'])->name('equipos.baja');
Route::put('/moviles/{id}/baja', [DispositivoMovilController::class, 'darDeBaja'])->name('moviles.baja');