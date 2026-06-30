<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Equipo;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function create()
    {
        // Cargar equipos con código, marca y modelo
        $equipos = Equipo::select('codigo_interno', 'marca', 'modelo')
            ->orderBy('codigo_interno')
            ->get();

        // Empleados activos del corporativo (tickets). numero_empleado/nombre_completo
        // son accesores del modelo, por eso se cargan filas completas (sin select()).
        $empleados = Empleado::activos()->orderBy('nombre')->get();

        return view('reportes.create', compact('equipos', 'empleados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula' => 'required|string|exists:equipos,codigo_interno',
            'numero_empleado' => 'required|exists:tickets.tbl_empleados,id_emp',
            'inconsistencias' => 'nullable|string|max:500',
            'tipo' => 'required|in:entrada,salida',
        ]);

        // El área se toma automáticamente del usuario autenticado
        $area = auth()->user()->area;

        if (!$area) {
            return back()->with('error', 'Tu perfil no tiene un área asignada. Contacta al administrador.');
        }

        $reporte = Reporte::create([
            'matricula' => $request->matricula,
            'area' => $area,
            'numero_empleado' => $request->numero_empleado,
            'inconsistencias' => $request->inconsistencias,
            'tipo' => $request->tipo,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('reportes.create')
            ->with('success', ' Reporte registrado correctamente');
    }

    public function index(Request $request)
    {
        $query = Reporte::with(['user', 'empleado']);

        // Seguridad solo ve sus propios reportes
        if (Auth::user()->role === 'seguridad') {
            $query->where('user_id', Auth::id());
        }

        $reportes = $query
            ->when($request->buscar, function($q, $buscar) {
                $q->where('matricula', 'like', "%{$buscar}%")
                  ->orWhere('numero_empleado', 'like', "%{$buscar}%");
            })
            ->when($request->area, fn($q, $area) => $q->where('area', $area))
            ->when($request->tipo, fn($q, $tipo) => $q->where('tipo', $tipo))
            ->when($request->fecha, fn($q, $fecha) => $q->whereDate('created_at', $fecha))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('reportes.index', compact('reportes'));
    }

    public function exportar()
    {
        $reportes = Reporte::with('user')->latest()->get();
        
        $csv = "ID,Fecha,Usuario,Empleado,N° Empleado,Matrícula,Área,Tipo,Inconsistencias\n";
        foreach ($reportes as $r) {
            $nombreEmpleado = $r->empleado ? $r->empleado->nombre_completo : 'N/A';
            $csv .= "{$r->id},{$r->created_at},{$r->user->name},{$nombreEmpleado},{$r->numero_empleado},{$r->matricula},{$r->area},{$r->tipo},{$r->inconsistencias}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reportes.csv"',
        ]);
    }
}