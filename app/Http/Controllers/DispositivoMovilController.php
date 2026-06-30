<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DispositivoMovil;
use App\Models\Empleado;
use App\Models\AsignacionMovil;

class DispositivoMovilController extends Controller
{

    /* ==========================================
       LISTAR TODOS LOS DISPOSITIVOS (CON FILTROS Y PENDIENTE)
    ========================================== */
    public function index(Request $request)
    {
        $query = DispositivoMovil::with(['ultimaAsignacion.empleado']);

        // Búsqueda por texto
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_interno', 'like', "%{$q}%")
                    ->orWhere('marca', 'like', "%{$q}%")
                    ->orWhere('modelo', 'like', "%{$q}%")
                    ->orWhere('imei', 'like', "%{$q}%")
                    ->orWhere('numero_sim', 'like', "%{$q}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por marca
        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        // Ordenamiento
        switch ($request->orden) {
            case 'antiguo':
                $query->orderBy('created_at', 'asc');
                break;
            case 'marca':
                $query->orderBy('marca')->orderBy('modelo');
                break;
            case 'estado':
                $query->orderBy('estado')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $moviles = $query->paginate(15)->withQueryString();

        // Obtener marcas para filtros
        $marcas = DispositivoMovil::distinct()->pluck('marca')->filter()->values();

        return view('moviles.index', compact('moviles', 'marcas'));
    }

    /* ==========================================
       DISPOSITIVOS DISPONIBLES
    ========================================== */
    public function disponibles()
    {
        $moviles = DispositivoMovil::where('asignado', false)
                    ->where('estado', '!=', 'Baja')
                    ->orderBy('marca')
                    ->get();

        return view('moviles.disponibles', compact('moviles'));
    }

    /* ==========================================
       FORMULARIO CREAR DISPOSITIVO (NUEVO)
    ========================================== */
    public function create()
    {
        return view('moviles.create');
    }

    /* ==========================================
       FORMULARIO ASIGNACIÓN - SOLO EMPLEADOS CON ROL
    ========================================== */
    public function createAsignacion($id)
    {
        $movil = DispositivoMovil::findOrFail($id);

        // Empleados activos del corporativo (tickets); se puede asignar a cualquiera.
        $empleados = Empleado::activos()->orderBy('nombre')->get();

        return view('asignaciones_moviles.create', compact('movil', 'empleados'));
    }

    /* ==========================================
       GUARDAR DISPOSITIVO
    ========================================== */
    public function store(Request $request)
    {
        $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'imei' => 'required|string|unique:dispositivos_moviles,imei',
            'numero_sim' => 'nullable|string|max:20',
            'numero_telefono' => 'nullable|string|max:20',
            'caracteristicas' => 'nullable|string|max:500',
        ]);

        // Generar código interno automático
        $ultimo = DispositivoMovil::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo_interno ?? 'MOV-0000', -4)) + 1 : 1;
        $codigoInterno = 'MOV-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

        DispositivoMovil::create([
            'codigo_interno' => $codigoInterno,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'imei' => $request->imei,
            'numero_sim' => $request->numero_sim,
            'numero_telefono' => $request->numero_telefono,
            'caracteristicas' => $request->caracteristicas,
            'estado' => 'Disponible',
            'asignado' => false
        ]);

        return redirect()->route('moviles.index')
            ->with('success', 'Dispositivo creado correctamente');
    }

    /* ==========================================
       EDITAR
    ========================================== */
    public function edit(DispositivoMovil $movil)
    {
        return view('moviles.edit', compact('movil'));
    }

    /* ==========================================
       ACTUALIZAR
    ========================================== */
    public function update(Request $request, DispositivoMovil $movil)
    {
        $request->validate([
            'marca' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'imei' => "required|string|unique:dispositivos_moviles,imei,{$movil->id}",
            'numero_sim' => 'nullable|string|max:20',
            'numero_telefono' => 'nullable|string|max:20',
            'caracteristicas' => 'nullable|string|max:500',
        ]);

        $movil->update([
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'imei' => $request->imei,
            'numero_sim' => $request->numero_sim,
            'numero_telefono' => $request->numero_telefono,
            'caracteristicas' => $request->caracteristicas,
        ]);

        return redirect()->route('moviles.index')
            ->with('success', 'Dispositivo actualizado correctamente');
    }

    /* ==========================================
       DAR DE BAJA - NUEVO MÉTODO
    ========================================== */
    public function darDeBaja(Request $request, $id)
    {
        $movil = DispositivoMovil::findOrFail($id);
        
        // Verificar que no esté asignado actualmente
        if ($movil->asignado) {
            return redirect()->route('moviles.index')
                ->with('error', ' No se puede dar de baja un dispositivo que está actualmente asignado.');
        }
        
        // Verificar que no esté ya dado de baja
        if ($movil->estado === 'Baja') {
            return redirect()->route('moviles.index')
                ->with('error', ' Este dispositivo ya está dado de baja.');
        }
        
        $request->validate([
            'motivo_baja' => 'required|string|min:5|max:500'
        ]);
        
        $movil->update([
            'estado' => 'Baja',
            'asignado' => false,
            'fecha_baja' => now(),
            'motivo_baja' => $request->motivo_baja
        ]);
        
        return redirect()->route('moviles.index')
            ->with('success', ' Dispositivo móvil dado de baja correctamente.');
    }

    /* ==========================================
       ELIMINAR (ya no se usa, pero se mantiene)
    ========================================== */
    public function destroy(DispositivoMovil $movil)
    {
        if ($movil->asignado) {
            return redirect()->route('moviles.index')
                ->with('error', 'No se puede eliminar un dispositivo asignado');
        }

        $movil->delete();

        return redirect()->route('moviles.index')
            ->with('success', 'Dispositivo eliminado correctamente');
    }

    /* ==========================================
       HISTORIAL
    ========================================== */
    public function historial(DispositivoMovil $movil)
    {
        $historial = $movil->asignaciones()
            ->with('empleado')
            ->orderBy('fecha_asignacion', 'desc')
            ->get();

        return view('moviles.historial', compact('movil', 'historial'));
    }

    /* ==========================================
       VER DETALLES DEL DISPOSITIVO
    ========================================== */
    public function show(DispositivoMovil $movil)
    {
        $asignacionActual = $movil->asignaciones()
            ->with('empleado')
            ->whereNull('fecha_devolucion')
            ->first();

        return view('moviles.show', compact('movil', 'asignacionActual'));
    }
}