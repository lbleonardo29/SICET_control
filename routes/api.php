<?php

use App\Http\Controllers\EquipoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsignacionController;

/* |--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rutas para Asignaciones


Route::get('/equipos/disponibles', [EquipoController::class, 'disponibles'])
->name('equipos.disponibles');
Route::get('/asignaciones', [AsignacionController::class, 'index']);
Route::post('/asignaciones', [AsignacionController::class, 'store']);
Route::put('/asignaciones/devolver/{id}', [AsignacionController::class, 'devolver']);
Route::middleware('auth')->group(function () {
    Route::get('/asignaciones/historial/empleado/{empleado_id}', [AsignacionController::class, 'historialEmpleado']);
});

