<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Mail\AsignacionPendiente;
use App\Notifications\SistemaNotificacion;

class AsignacionController extends Controller
{

    /* =====================================================
        LISTADO DE ASIGNACIONES
    ===================================================== */

    public function index()
    {
        $asignaciones = Asignacion::whereNull('fecha_devolucion')
            ->where('estado_asignacion', 'aceptada')
            ->with(['empleado.planta', 'equipo'])
            ->orderBy('fecha_asignacion', 'desc')
            ->get();

        $empleados = Empleado::where('activo', 1)->get();
        $equiposDisponibles = Equipo::where('estado', 'Disponible')->get();

        return view('asignaciones.index', compact('asignaciones', 'empleados', 'equiposDisponibles'));
    }

    /* =====================================================
        DASHBOARD - SOLO ASIGNACIONES ACEPTADAS
    ===================================================== */

    public function dashboard(Request $request)
    {
        $query = Asignacion::with(['empleado', 'equipo'])
            ->where('estado_asignacion', 'aceptada');

        // Buscador
        if ($request->q) {
            $query->where(function ($subquery) use ($request) {
                $subquery->whereHas('empleado', function ($q) use ($request) {
                    $q->where('nombre_completo', 'LIKE', '%' . $request->q . '%')
                      ->orWhere('numero_empleado', 'LIKE', '%' . $request->q . '%');
                })->orWhereHas('equipo', function ($q) use ($request) {
                    $q->where('marca', 'like', '%' . $request->q . '%')
                      ->orWhere('modelo', 'like', '%' . $request->q . '%')
                      ->orWhere('codigo_interno', 'like', '%' . $request->q . '%');
                });
            });
        }

        // Filtro empleado
        if ($request->empleado_id) {
            $query->where('empleado_id', $request->empleado_id);
        }

        // Filtro equipo
        if ($request->equipo_id) {
            $query->where('equipo_id', $request->equipo_id);
        }

        // Estado - POR DEFECTO MUESTRA SOLO ACTIVOS
        if ($request->estado == 'activo') {
            $query->whereNull('fecha_devolucion');
        } elseif ($request->estado == 'devuelto') {
            $query->whereNotNull('fecha_devolucion');
        } else {
            $query->whereNull('fecha_devolucion');
        }

        $asignaciones = $query
            ->orderBy('fecha_asignacion', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Datos para los filtros de la vista
        $empleadosFiltro = Empleado::where('activo', 1)->get();
        $equiposFiltro = Equipo::all();

        return view('asignaciones.dashboard', compact('asignaciones', 'empleadosFiltro', 'equiposFiltro'));
    }

    /* =====================================================
        FORMULARIO DE ASIGNACIÓN (CREATE)
    ===================================================== */

    public function create($equipo)
    {
        $equipo = Equipo::findOrFail($equipo);
        
        if ($equipo->estado !== 'Disponible') {
            return redirect()->route('equipos.disponibles')
                ->with('error', 'Esta computadora no está disponible para asignación.');
        }

        // EMPLEADOS ACTIVOS QUE TENGAN USUARIO
        $empleados = Empleado::where('activo', 1)
            ->whereHas('user', function($q) {
                $q->whereIn('role', ['admin', 'user', 'rh']);
            })
            ->get();

        return view('asignaciones.create', compact('equipo', 'empleados'));
    }

    /* =====================================================
        GUARDAR ASIGNACIÓN (STORE) - CON ESTADO PENDIENTE
        LÍMITE: 3 COMPUTADORAS POR EMPLEADO
    ===================================================== */

    public function store(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'empleado_id' => 'required|exists:empleados,id',
            'fecha_asignacion' => 'required|date|before_or_equal:today',
        ]);

        // Verificar si el equipo tiene una asignación ACEPTADA activa
        $asignacionActiva = Asignacion::where('equipo_id', $request->equipo_id)
            ->whereNull('fecha_devolucion')
            ->where('estado_asignacion', 'aceptada')
            ->exists();
            
        if ($asignacionActiva) {
            return back()->with('error', 'Esta computadora ya está asignada a otro empleado.');
        }

        // Verificar si el equipo tiene una asignación PENDIENTE (en espera)
        $asignacionPendiente = Asignacion::where('equipo_id', $request->equipo_id)
            ->where('estado_asignacion', 'pendiente')
            ->exists();
            
        if ($asignacionPendiente) {
            return back()->with('error', 'Esta computadora ya tiene una asignación pendiente de respuesta.');
        }

        // NUEVO: Verificar cuántas computadoras activas tiene el empleado (máximo 3)
        $totalActivas = Asignacion::where('empleado_id', $request->empleado_id)
            ->whereNull('fecha_devolucion')
            ->whereIn('estado_asignacion', ['pendiente', 'aceptada'])
            ->count();

        if ($totalActivas >= 3) {
            return back()->with('error', 'El empleado ya tiene 3 computadoras asignadas. No puede tener más.');
        }

        // Verificar que el equipo existe y está disponible
        $equipo = Equipo::findOrFail($request->equipo_id);
        if ($equipo->estado !== 'Disponible') {
            return back()->with('error', 'La computadora no está disponible.');
        }

        // Buscar el usuario asociado al empleado
        $user = User::where('empleado_id', $request->empleado_id)->first();

        // Crear asignación con estado_asignacion = 'pendiente'
        $asignacion = Asignacion::create([
            'equipo_id' => $request->equipo_id,
            'empleado_id' => $request->empleado_id,
            'user_id' => $user ? $user->id : null,
            'fecha_asignacion' => $request->fecha_asignacion,
            'estado_asignacion' => 'pendiente',
        ]);

        // Cambiar estado del equipo a "Pendiente"
        $equipo->update(['estado' => 'Pendiente']);

        // Buscar empleado
        $empleado = Empleado::where('id', $request->empleado_id)->first();

        // =====================================================
        // GENERAR PDF
        // =====================================================
        try {
            if (!Storage::disk('public')->exists('cartas')) {
                Storage::disk('public')->makeDirectory('cartas');
            }

            $pdf = Pdf::loadView('pdf.carta_asignacion', [
                'equipo' => $equipo,
                'empleado' => $empleado,
                'asignacion' => $asignacion
            ]);

            $nombre = 'carta_equipo_' . $asignacion->id . '.pdf';
            $ruta = 'cartas/' . $nombre;

            Storage::disk('public')->put($ruta, $pdf->output());
            $asignacion->update(['carta_pdf' => $ruta]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF: ' . $e->getMessage());
        }

        // =====================================================
        // ENVIAR CORREO DE NOTIFICACIÓN (sin enlaces)
        // =====================================================
        if ($empleado && $empleado->correo) {
            try {
                Mail::to($empleado->correo)->send(new AsignacionPendiente($asignacion, 'equipo'));
            } catch (\Exception $e) {
                \Log::error('Error al enviar correo: ' . $e->getMessage());
            }
        }

        // =====================================================
        // NOTIFICACIÓN INTERNA (campana) al empleado, si tiene cuenta
        // =====================================================
        if ($user) {
            $user->notify(new SistemaNotificacion(
                'Nueva asignación pendiente',
                "Se te asignó la computadora {$equipo->codigo_interno} ({$equipo->marca} {$equipo->modelo}). Acéptala o recházala desde tu panel.",
                route('dashboard'),
                'pc-display',
                'info'
            ));
        }

        return redirect()
            ->route('asignaciones.dashboard')
            ->with('success', ' Asignación creada en estado "En espera". El empleado deberá aceptarla desde el sistema.');
    }

    /* =====================================================
        ACEPTAR ASIGNACIÓN (desde el sistema)
    ===================================================== */

    public function aceptar($id)
    {
        $asignacion = Asignacion::with('equipo')->findOrFail($id);
        
        // Verificar que la asignación esté pendiente
        if ($asignacion->estado_asignacion !== 'pendiente') {
            return back()->with('error', 'Esta asignación ya fue respondida.');
        }
        
        // Verificar que el equipo está pendiente
        if ($asignacion->equipo->estado !== 'Pendiente') {
            return back()->with('error', 'La computadora no está en estado pendiente.');
        }
        
        // Verificar que el usuario autenticado es el empleado asignado
        $user = Auth::user();
        if ($user->empleado_id != $asignacion->empleado_id) {
            return back()->with('error', 'No tienes permiso para aceptar esta asignación.');
        }
        
        // Actualizar asignación
        $asignacion->update([
            'estado_asignacion' => 'aceptada',
            'fecha_respuesta' => now(),
        ]);
        
        // Cambiar estado del equipo a "Asignado"
        $asignacion->equipo->update(['estado' => 'Asignado']);

        // Notificar a los administradores
        Notification::send(
            User::where('role', 'admin')->get(),
            new SistemaNotificacion(
                'Asignación aceptada',
                ($asignacion->empleado->nombre_completo ?? 'Un empleado') . " aceptó la computadora {$asignacion->equipo->codigo_interno}.",
                route('asignaciones.dashboard'),
                'check-circle',
                'success'
            )
        );

        return redirect()->route('dashboard')
            ->with('success', ' Has aceptado la computadora ' . $asignacion->equipo->codigo_interno);
    }

    /* =====================================================
        FIRMAR Y ACEPTAR (firma electrónica desde el modal)
    ===================================================== */

    public function firmar(Request $request, $id)
    {
        $request->validate([
            'firma' => ['required', 'string', 'regex:/^data:image\/png;base64,/'],
        ], [
            'firma.required' => 'Debes firmar antes de aceptar.',
            'firma.regex'    => 'La firma recibida no es válida.',
        ]);

        $asignacion = Asignacion::with(['equipo', 'empleado'])->findOrFail($id);

        if ($asignacion->estado_asignacion !== 'pendiente') {
            return back()->with('error', 'Esta asignación ya fue respondida.');
        }

        $user = Auth::user();
        if ($user->empleado_id != $asignacion->empleado_id) {
            return back()->with('error', 'No tienes permiso para firmar esta asignación.');
        }

        // Guardar firma + aceptar
        $asignacion->update([
            'estado_asignacion' => 'aceptada',
            'fecha_respuesta'   => now(),
            'firma'             => $request->firma,
            'fecha_firma'       => now(),
        ]);

        $asignacion->equipo->update(['estado' => 'Asignado']);

        // Regenerar la carta responsiva CON la firma incrustada
        try {
            if (!Storage::disk('public')->exists('cartas')) {
                Storage::disk('public')->makeDirectory('cartas');
            }
            $pdf = Pdf::loadView('pdf.carta_asignacion', [
                'equipo'     => $asignacion->equipo,
                'empleado'   => $asignacion->empleado,
                'asignacion' => $asignacion,
            ]);
            $ruta = 'cartas/carta_equipo_' . $asignacion->id . '.pdf';
            Storage::disk('public')->put($ruta, $pdf->output());
            $asignacion->update(['carta_pdf' => $ruta]);
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF firmado: ' . $e->getMessage());
        }

        // Notificar a los administradores
        Notification::send(
            User::where('role', 'admin')->get(),
            new SistemaNotificacion(
                'Asignación firmada',
                ($asignacion->empleado->nombre_completo ?? 'Un empleado') . " firmó y aceptó la computadora {$asignacion->equipo->codigo_interno}.",
                route('asignaciones.dashboard'),
                'check-circle',
                'success'
            )
        );

        return redirect()->route('dashboard')
            ->with('success', ' Firmaste y aceptaste la computadora ' . $asignacion->equipo->codigo_interno . '. Ya puedes descargar tu carta responsiva.');
    }

    /* =====================================================
        RECHAZAR ASIGNACIÓN (desde el sistema)
    ===================================================== */

    public function rechazar($id)
    {
        $asignacion = Asignacion::with('equipo')->findOrFail($id);
        
        // Verificar que la asignación esté pendiente
        if ($asignacion->estado_asignacion !== 'pendiente') {
            return back()->with('error', 'Esta asignación ya fue respondida.');
        }
        
        // Verificar que el usuario autenticado es el empleado asignado
        $user = Auth::user();
        if ($user->empleado_id != $asignacion->empleado_id) {
            return back()->with('error', 'No tienes permiso para rechazar esta asignación.');
        }
        
        // Actualizar asignación
        $asignacion->update([
            'estado_asignacion' => 'rechazada',
            'fecha_respuesta' => now(),
            'fecha_devolucion' => now(),
        ]);
        
        // Volver a Disponible cuando rechaza
        $asignacion->equipo->update(['estado' => 'Disponible']);

        // Notificar a los administradores
        Notification::send(
            User::where('role', 'admin')->get(),
            new SistemaNotificacion(
                'Asignación rechazada',
                ($asignacion->empleado->nombre_completo ?? 'Un empleado') . " rechazó la computadora {$asignacion->equipo->codigo_interno}.",
                route('asignaciones.dashboard'),
                'x-circle',
                'warning'
            )
        );

        return redirect()->route('dashboard')
            ->with('info', ' Has rechazado la computadora ' . $asignacion->equipo->codigo_interno);
    }

    /* =====================================================
        DEVOLVER (solo para asignaciones ACEPTADAS)
    ===================================================== */

    public function devolver($id)
    {
        $asignacion = Asignacion::with('equipo')->findOrFail($id);

        if ($asignacion->fecha_devolucion) {
            return back()->with('error', 'Esta computadora ya fue devuelta.');
        }
        
        if ($asignacion->estado_asignacion !== 'aceptada') {
            return back()->with('error', 'Solo se pueden devolver asignaciones aceptadas.');
        }

        $asignacion->update([
            'fecha_devolucion' => now(),
        ]);

        $asignacion->equipo->update(['estado' => 'Disponible']);

        return back()->with('success', ' Computadora devuelta correctamente.');
    }

    /* =====================================================
        VER PDF (CARTA RESPONSIVA)
    ===================================================== */

    public function cartaResponsiva($equipo_id)
    {
        $asignacion = Asignacion::where('equipo_id', $equipo_id)
            ->where('estado_asignacion', 'aceptada')
            ->with(['empleado', 'equipo'])
            ->latest('fecha_asignacion')
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.carta_asignacion', [
            'equipo' => $asignacion->equipo,
            'empleado' => $asignacion->empleado,
            'asignacion' => $asignacion
        ]);

        return $pdf->stream('carta_responsiva_equipo_' . $asignacion->id . '.pdf');
    }

    /* =====================================================
        DESCARGAR PDF
    ===================================================== */

    public function descargar($id)
    {
        $asignacion = Asignacion::findOrFail($id);

        // Solo admin o el empleado dueño de la asignación
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->empleado_id != $asignacion->empleado_id) {
            abort(403, 'No tienes permiso para descargar esta carta.');
        }

        if (!$asignacion->carta_pdf) {
            return back()->with('error', 'No hay PDF disponible. La carta se genera al firmar la asignación.');
        }

        if (!Storage::disk('public')->exists($asignacion->carta_pdf)) {
            return back()->with('error', 'El archivo PDF no existe.');
        }

        return Storage::disk('public')->download($asignacion->carta_pdf);
    }

    /* =====================================================
        ELIMINAR (solo asignaciones rechazadas o devueltas)
    ===================================================== */

    public function destroy($id)
    {
        $asignacion = Asignacion::findOrFail($id);

        if (!$asignacion->fecha_devolucion && $asignacion->estado_asignacion === 'aceptada') {
            return back()->with('error', 'No se puede eliminar una asignación activa.');
        }

        if ($asignacion->carta_pdf && Storage::disk('public')->exists($asignacion->carta_pdf)) {
            Storage::disk('public')->delete($asignacion->carta_pdf);
        }

        $asignacion->delete();

        return back()->with('success', ' Asignación eliminada correctamente.');
    }

    /* =====================================================
        BUSCAR EMPLEADOS (API)
    ===================================================== */

    public function buscarEmpleados(Request $request)
    {
        $term = $request->get('term');
        
        $empleados = Empleado::where('activo', 1)
            ->whereHas('user', function($q) {
                $q->whereIn('role', ['admin', 'user', 'rh']);
            })
            ->where(function ($q) use ($term) {
                $q->where('nombre_completo', 'LIKE', "%{$term}%")
                  ->orWhere('numero_empleado', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'nombre_completo', 'numero_empleado']);

        return response()->json($empleados);
    }
}