<?php

use Illuminate\Support\Facades\Route;

/* |--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| Las rutas de asignaciones/equipos que vivían aquí eran duplicados sin uso
| de las de routes/web.php (una incluso rota: apuntaba a un método
| inexistente, EquipoController::disponibles). Además duplicaban acciones de
| asignaciones sin el middleware de rol admin que sí tienen en web.php. Se
| retiraron por completo; los endpoints reales usados por el frontend viven
| en el grupo "API (autenticado)" de routes/web.php.
|
*/
