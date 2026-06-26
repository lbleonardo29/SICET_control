<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\Planta;
use App\Models\DispositivoMovil;
use Illuminate\Pagination\LengthAwarePaginator;

class EquipoController extends Controller
{
    /**
     * Catálogo UNIFICADO: computadoras (equipos) + móviles (dispositivos_moviles).
     * Las tablas siguen separadas; aquí se normalizan a una forma común para
     * listarlos juntos con su etiqueta de tipo. Filtros: q, tipo, estado.
     */
    public function catalogo(Request $request)
    {
        $q      = trim((string) $request->q);
        $tipo   = $request->tipo;   // computadora | movil | (vacío = ambos)
        $estado = $request->estado; // Disponible | Asignado | Mantenimiento | Pendiente | Baja

        $items = collect();

        // ---- Computadoras ----
        if ($tipo !== 'movil') {
            foreach (Equipo::with('planta')->get() as $e) {
                $items->push((object) [
                    'tipo'                => 'computadora',
                    'id'                  => $e->id,
                    'codigo_interno'      => $e->codigo_interno,
                    'nombre'              => $e->nombre_equipo,
                    'marca'               => $e->marca,
                    'modelo'              => $e->modelo,
                    'identificador'       => $e->numero_serie,
                    'identificador_label' => 'Serie',
                    'estado'              => $e->estado,
                    'planta'              => optional($e->planta)->nombre,
                    'created_at'          => $e->created_at,
                    'show_url'            => route('equipos.show', $e->id),
                    'edit_url'            => route('equipos.edit', $e->id),
                    'historial_url'       => route('equipos.historial', $e->id),
                ]);
            }
        }

        // ---- Móviles (normaliza estado a partir del booleano 'asignado') ----
        if ($tipo !== 'computadora') {
            foreach (DispositivoMovil::all() as $m) {
                $estadoMovil = $m->estado === 'Baja'
                    ? 'Baja'
                    : ($m->asignado ? 'Asignado' : 'Disponible');

                $items->push((object) [
                    'tipo'                => 'movil',
                    'id'                  => $m->id,
                    'codigo_interno'      => $m->codigo_interno,
                    'nombre'              => trim("{$m->marca} {$m->modelo}"),
                    'marca'               => $m->marca,
                    'modelo'              => $m->modelo,
                    'identificador'       => $m->imei,
                    'identificador_label' => 'IMEI',
                    'estado'              => $estadoMovil,
                    'planta'              => null,
                    'created_at'          => $m->created_at,
                    'show_url'            => route('moviles.show', $m->id),
                    'edit_url'            => route('moviles.edit', $m->id),
                    'historial_url'       => route('moviles.historial', $m->id),
                ]);
            }
        }

        // ---- Filtro de texto ----
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = $items->filter(function ($it) use ($needle) {
                $heno = mb_strtolower(implode(' ', array_filter([
                    $it->codigo_interno, $it->nombre, $it->marca,
                    $it->modelo, $it->identificador,
                ])));
                return str_contains($heno, $needle);
            });
        }

        // ---- Filtro por estado ----
        if ($estado) {
            $items = $items->filter(fn ($it) => $it->estado === $estado);
        }

        // ---- Orden: más recientes primero ----
        $items = $items->sortByDesc(fn ($it) => optional($it->created_at)->timestamp ?? 0)->values();

        // ---- Paginación manual sobre la colección combinada ----
        $perPage = 15;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $equipos = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Conteos para las tarjetas/resumen
        $totalComputadoras = Equipo::count();
        $totalMoviles      = DispositivoMovil::count();

        return view('equipos.catalogo', compact('equipos', 'totalComputadoras', 'totalMoviles'));
    }

    /**
     * Mostrar todos los equipos con filtros y paginación
     */
    public function index(Request $request)
    {
        $query = Equipo::with(['planta', 'ultimaAsignacion.empleado']);

        // Búsqueda por texto
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_equipo', 'like', "%{$q}%")
                    ->orWhere('codigo_interno', 'like', "%{$q}%")
                    ->orWhere('marca', 'like', "%{$q}%")
                    ->orWhere('modelo', 'like', "%{$q}%")
                    ->orWhere('numero_serie', 'like', "%{$q}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por planta
        if ($request->filled('planta_id')) {
            $query->where('planta_id', $request->planta_id);
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
            default: // 'reciente'
                $query->orderBy('created_at', 'desc');
        }

        // Paginación
        $equipos = $query->paginate(15)->withQueryString();

        // Obtener listas para filtros
        $estados = Equipo::distinct()->pluck('estado')->filter();
        $plantas = Planta::all();

        return view('equipos.index', compact('equipos', 'estados', 'plantas'));
    }

    /**
     * Formulario para registrar equipo
     */
    public function create()
    {
        $plantas = Planta::all();
        return view('equipos.create', compact('plantas'));
    }

    /**
     * Guardar nuevo equipo
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_equipo'              => 'required|string|max:100|unique:equipos,nombre_equipo',
            'marca'                      => 'required|string|max:100',
            'modelo'                     => 'required|string|max:100',
            'numero_serie'               => 'required|string|max:100|unique:equipos,numero_serie',
            'color'                      => 'nullable|string|max:50',
            'procesador'                 => 'required|string|max:100',
            'ram'                        => 'required|string|max:50',
            'tipo_almacenamiento'        => 'required|string|max:20',
            'capacidad_almacenamiento'   => 'required|string|max:20',
            'cargador'                   => 'required|boolean',
            'fecha_adquisicion'          => 'required|date|before_or_equal:today',
            'planta_id'                  => 'required|exists:plantas,id',
            'observaciones'              => 'required|string|max:1000',
        ]);

        // Generar código interno automático
        $ultimoEquipo = Equipo::orderBy('id', 'desc')->first();
        $nuevoCodigo = 'SICET-' . str_pad(($ultimoEquipo ? $ultimoEquipo->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        // Combinar tipo y capacidad para el campo ssd
        $almacenamiento = $request->tipo_almacenamiento . ' ' . $request->capacidad_almacenamiento;

        Equipo::create([
            'nombre_equipo'              => strtoupper($request->nombre_equipo),
            'codigo_interno'             => $nuevoCodigo,
            'marca'                      => strtoupper($request->marca),
            'modelo'                     => strtoupper($request->modelo),
            'numero_serie'               => strtoupper($request->numero_serie),
            'color'                      => strtoupper($request->color),
            'procesador'                 => strtoupper($request->procesador),
            'ram'                        => strtoupper($request->ram),
            'ssd'                        => $almacenamiento,
            'cargador'                   => $request->cargador,
            'fecha_adquisicion'          => $request->fecha_adquisicion,
            'planta_id'                  => $request->planta_id,
            'observaciones'              => strtoupper($request->observaciones),
            'estado'                     => 'Disponible',
        ]);

        return redirect()
            ->route('equipos.index')
            ->with('success', ' Computadora registrada correctamente');
    }

    /**
     * Equipos disponibles (vista simplificada)
     */
    public function disponiblesView(Request $request)
    {
        $query = Equipo::where('estado', 'Disponible');

        // Búsqueda
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_equipo', 'like', "%{$q}%")
                    ->orWhere('codigo_interno', 'like', "%{$q}%")
                    ->orWhere('marca', 'like', "%{$q}%")
                    ->orWhere('modelo', 'like', "%{$q}%")
                    ->orWhere('numero_serie', 'like', "%{$q}%");
            });
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
            default:
                $query->orderBy('created_at', 'desc');
        }

        $equipos = $query->get();

        // Obtener marcas únicas para el filtro
        $marcas = Equipo::where('estado', 'Disponible')
            ->distinct()
            ->pluck('marca')
            ->filter()
            ->values();

        return view('equipos.disponibles', compact('equipos', 'marcas'));
    }

    /**
     * Formulario edición
     */
    public function edit(Equipo $equipo)
    {
        $plantas = Planta::all();
        return view('equipos.edit', compact('equipo', 'plantas'));
    }

    /**
     * Actualizar equipo
     */
    public function update(Request $request, Equipo $equipo)
    {
        $request->validate([
            'nombre_equipo'              => 'required|string|max:100|unique:equipos,nombre_equipo,' . $equipo->id,
            'marca'                      => 'required|string|max:100',
            'modelo'                     => 'required|string|max:100',
            'numero_serie'               => 'required|string|max:100|unique:equipos,numero_serie,' . $equipo->id,
            'color'                      => 'nullable|string|max:50',
            'procesador'                 => 'required|string|max:100',
            'ram'                        => 'required|string|max:50',
            'tipo_almacenamiento'        => 'required|string|max:20',
            'capacidad_almacenamiento'   => 'required|string|max:20',
            'cargador'                   => 'required|boolean',
            'fecha_adquisicion'          => 'required|date|before_or_equal:today',
            'planta_id'                  => 'required|exists:plantas,id',
            'observaciones'              => 'required|string|max:1000',
        ]);

        // Combinar tipo y capacidad para el campo ssd
        $almacenamiento = $request->tipo_almacenamiento . ' ' . $request->capacidad_almacenamiento;

        $equipo->update([
            'nombre_equipo'              => strtoupper($request->nombre_equipo),
            'marca'                      => strtoupper($request->marca),
            'modelo'                     => strtoupper($request->modelo),
            'numero_serie'               => strtoupper($request->numero_serie),
            'color'                      => strtoupper($request->color),
            'procesador'                 => strtoupper($request->procesador),
            'ram'                        => strtoupper($request->ram),
            'ssd'                        => $almacenamiento,
            'cargador'                   => $request->cargador,
            'fecha_adquisicion'          => $request->fecha_adquisicion,
            'planta_id'                  => $request->planta_id,
            'observaciones'              => strtoupper($request->observaciones),
        ]);

        return redirect()
            ->route('equipos.index')
            ->with('success', ' Computadora actualizada correctamente');
    }

    /**
     * DAR DE BAJA un equipo
     */
    public function darDeBaja(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        
        // Verificar que no esté asignado actualmente
        if ($equipo->asignaciones()->whereNull('fecha_devolucion')->exists()) {
            return redirect()
                ->route('equipos.index')
                ->with('error', ' No se puede dar de baja un equipo que está actualmente asignado.');
        }
        
        // Verificar que no esté ya dado de baja
        if ($equipo->estado === 'Baja') {
            return redirect()
                ->route('equipos.index')
                ->with('error', ' Este equipo ya está dado de baja.');
        }
        
        $request->validate([
            'motivo_baja' => 'required|string|min:5|max:500'
        ]);
        
        $equipo->update([
            'estado' => 'Baja',
            'fecha_baja' => now(),
            'motivo_baja' => $request->motivo_baja
        ]);
        
        return redirect()
            ->route('equipos.index')
            ->with('success', ' Equipo dado de baja correctamente.');
    }

    /**
     * ELIMINAR equipo (ya no se usa, pero se mantiene por compatibilidad)
     */
    public function destroy(Equipo $equipo)
    {
        if ($equipo->asignaciones()->whereNull('fecha_devolucion')->exists()) {
            return redirect()
                ->route('equipos.index')
                ->with('error', ' No se puede eliminar la computadora porque está asignada actualmente.');
        }

        $equipo->asignaciones()->delete();
        $equipo->delete();

        return redirect()
            ->route('equipos.index')
            ->with('success', ' Computadora eliminada correctamente');
    }

    /**
     * Historial de asignaciones
     */
    public function historial(Equipo $equipo)
    {
        $asignaciones = $equipo->asignaciones()
            ->with('empleado')
            ->orderByDesc('fecha_asignacion')
            ->paginate(15);

        return view('equipos.historial', compact('equipo', 'asignaciones'));
    }

    /**
     * Ver detalles de un equipo
     */
    public function show(Equipo $equipo)
    {
        $asignacionActual = $equipo->asignaciones()
            ->with('empleado')
            ->whereNull('fecha_devolucion')
            ->first();

        return view('equipos.show', compact('equipo', 'asignacionActual'));
    }
}