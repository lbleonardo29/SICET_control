<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\User;

/**
 * Empleados = DIRECTORIO DE SOLO LECTURA.
 *
 * Los empleados son maestro del corporativo (tickets.tbl_empleados, solo
 * lectura). La tabla local `empleados` de SICET fue eliminada, por lo que ya no
 * se pueden dar de alta, editar, activar/desactivar ni eliminar desde SICET.
 */
class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Listar empleados (directorio de consulta) con búsqueda y filtro de estado.
    public function index(Request $request)
    {
        $query = Empleado::query();

        if ($request->filled('q')) {
            $query->buscar($request->q);
        }

        $empleados = $query->orderBy('id_emp', 'asc')->get();

        // Vincular la cuenta de usuario (si existe) SIN eager-load cruzado de conexión:
        // el empleado vive en `tickets` y users en la BD local, así que se consulta aparte.
        $numeros = $empleados->pluck('id_emp')->map(fn ($n) => (string) $n)->all();
        $users = User::whereIn('numero_empleado', $numeros)->get()->keyBy('numero_empleado');
        $empleados->each(fn ($e) => $e->setRelation('user', $users->get((string) $e->id_emp)));

        return view('empleados.index', compact('empleados'));
    }
}
