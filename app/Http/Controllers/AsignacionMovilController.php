<?php

namespace App\Http\Controllers;

use App\Models\AsignacionMovil;
use App\Models\DispositivoMovil;
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

class AsignacionMovilController extends Controller
{
    /* =====================================================
       DASHBOARD - SOLO ASIGNACIONES ACEPTADAS
    ===================================================== */
    public function dashboard(Request $request)
    {
        $query = AsignacionMovil::with(['dispositivo', 'empleado'])
            ->whereIn('estado_asignacion', ['pendiente', 'aceptada']);

        // Buscador. El empleado vive en `tickets` (otra conexión): no se puede usar
        // whereHas('empleado'). Se resuelven los id_emp que coinciden y se filtra.
        if ($request->q) {
            $term = $request->q;
            $idsEmp = Empleado::buscar($term)->pluck('id_emp')->all();
            $query->where(function ($sub) use ($term, $idsEmp) {
                $sub->whereIn('empleado_id', empty($idsEmp) ? [-1] : $idsEmp)
                    ->orWhereHas('dispositivo', function ($q) use ($term) {
                        $q->where('marca', 'like', '%' . $term . '%')
                          ->orWhere('modelo', 'like', '%' . $term . '%')
                          ->orWhere('imei', 'like', '%' . $term . '%')
                          ->orWhere('codigo_interno', 'like', '%' . $term . '%');
                    });
            });
        }

        // Filtro empleado
        if ($request->empleado_id) {
            $query->where('empleado_id', $request->empleado_id);
        }

        // Filtro dispositivo
        if ($request->dispositivo_id) {
            $query->where('dispositivo_movil_id', $request->dispositivo_id);
        }

        // Estado
        if ($request->estado == 'activo') {
            $query->whereNull('fecha_devolucion');
        } elseif ($request->estado == 'devuelto') {
            $query->whereNotNull('fecha_devolucion');
        } else {
            $query->whereNull('fecha_devolucion');
        }

        $asignaciones = $query
            ->orderByDesc('fecha_asignacion')
            ->paginate(10)
            ->withQueryString();

        // Datos para los filtros
        $empleadosFiltro = Empleado::activos()->orderBy('nombre')->get();
        $dispositivosFiltro = DispositivoMovil::all();

        return view('asignaciones_moviles.dashboard', compact('asignaciones', 'empleadosFiltro', 'dispositivosFiltro'));
    }            

    /* =====================================================
       FORMULARIO ASIGNACIÓN - SOLO EMPLEADOS CON ROL
    ===================================================== */
    public function create($id)
    {
        $movil = DispositivoMovil::findOrFail($id);
        
        // Verificar si tiene asignación ACEPTADA activa
        $asignacionActiva = AsignacionMovil::where('dispositivo_movil_id', $id)
            ->whereNull('fecha_devolucion')
            ->where('estado_asignacion', 'aceptada')
            ->exists();
            
        if ($asignacionActiva) {
            return redirect()->route('moviles.disponibles')
                ->with('error', 'Este dispositivo ya está asignado.');
        }
        
        // Verificar si tiene asignación PENDIENTE
        $asignacionPendiente = AsignacionMovil::where('dispositivo_movil_id', $id)
            ->where('estado_asignacion', 'pendiente')
            ->exists();
            
        if ($asignacionPendiente) {
            return redirect()->route('moviles.disponibles')
                ->with('error', 'Este dispositivo ya tiene una asignación pendiente de respuesta.');
        }
        
        // Empleados activos del corporativo (tickets). Se puede asignar a cualquiera;
        // si aún no tiene cuenta, verá la asignación al iniciar sesión por primera vez.
        $empleados = Empleado::activos()->orderBy('nombre')->get();

        return view('asignaciones_moviles.create', compact('movil', 'empleados'));
    }

    /* =====================================================
       GUARDAR ASIGNACIÓN (STORE) - CON ESTADO PENDIENTE
    ===================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'dispositivo_movil_id' => 'required|exists:dispositivos_moviles,id',
            'empleado_id' => 'required|exists:tickets.tbl_empleados,id_emp',
            'fecha_asignacion' => 'required|date|before_or_equal:today',
        ]);

        // Verificar si el dispositivo tiene una asignación ACEPTADA activa
        $dispositivoActivo = AsignacionMovil::where('dispositivo_movil_id', $request->dispositivo_movil_id)
            ->whereNull('fecha_devolucion')
            ->where('estado_asignacion', 'aceptada')
            ->exists();

        if ($dispositivoActivo) {
            return back()->with('error', 'Este dispositivo ya está asignado a otro empleado.');
        }

        // Verificar si el dispositivo tiene una asignación PENDIENTE
        $dispositivoPendiente = AsignacionMovil::where('dispositivo_movil_id', $request->dispositivo_movil_id)
            ->where('estado_asignacion', 'pendiente')
            ->exists();

        if ($dispositivoPendiente) {
            return back()->with('error', 'Este dispositivo ya tiene una asignación pendiente de respuesta.');
        }

        // Verificar si el empleado ya tiene una asignación pendiente o aceptada
        $empleadoTienePendiente = AsignacionMovil::where('empleado_id', $request->empleado_id)
            ->whereNull('fecha_devolucion')
            ->whereIn('estado_asignacion', ['pendiente', 'aceptada'])
            ->exists();

        if ($empleadoTienePendiente) {
            return back()->with('error', 'El empleado ya tiene una asignación pendiente o activa.');
        }

        $movil = DispositivoMovil::findOrFail($request->dispositivo_movil_id);
        
        // Verificar que el dispositivo está disponible (no pendiente ni asignado)
        if ($movil->estado !== 'Disponible') {
            return back()->with('error', 'El dispositivo no está disponible.');
        }

        $empleado = Empleado::findOrFail($request->empleado_id);
        $user = User::where('numero_empleado', (string) $request->empleado_id)->first();

        // Crear asignación con estado_asignacion = 'pendiente'
        $asignacion = AsignacionMovil::create([
            'dispositivo_movil_id' => $request->dispositivo_movil_id,
            'empleado_id' => $request->empleado_id,
            'user_id' => $user ? $user->id : null,
            'fecha_asignacion' => $request->fecha_asignacion,
            'estado_asignacion' => 'pendiente',
        ]);

        // Cambiar estado del dispositivo a "Pendiente"
        $movil->update([
            'estado' => 'Pendiente',
            'asignado' => true
        ]);

        // =====================================================
        // GENERAR PDF
        // =====================================================
        try {
            if (!Storage::disk('public')->exists('cartas')) {
                Storage::disk('public')->makeDirectory('cartas');
            }

            $pdf = Pdf::loadView('pdf.carta_asignacion_movil', [
                'movil' => $movil,
                'empleado' => $empleado,
                'asignacion' => $asignacion
            ]);

            $nombre = 'carta_movil_' . $asignacion->id . '.pdf';
            $ruta = 'cartas/' . $nombre;

            Storage::disk('public')->put($ruta, $pdf->output());
            $asignacion->update(['carta_pdf' => $ruta]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF móvil: ' . $e->getMessage());
        }

        // =====================================================
        // ENVIAR CORREO DE NOTIFICACIÓN (sin enlaces)
        // =====================================================
        $emailDestino = ($empleado && $empleado->correo) ? $empleado->correo : ($user ? $user->email : null);
        if ($emailDestino) {
            try {
                Mail::to($emailDestino)->send(new AsignacionPendiente($asignacion, 'movil'));
            } catch (\Exception $e) {
                \Log::error('Error al enviar correo móvil: ' . $e->getMessage());
            }
        }

        // Notificación interna (campana) al empleado, si tiene cuenta
        if ($user) {
            $user->notify(new SistemaNotificacion(
                'Nueva asignación pendiente',
                "Se te asignó el dispositivo {$movil->codigo_interno} ({$movil->marca} {$movil->modelo}). Acéptalo o recházalo desde tu panel.",
                route('dashboard'),
                'phone',
                'info'
            ));
        }

        return redirect()
            ->route('asignaciones.moviles.dashboard')
            ->with('success', ' Asignación creada en estado "En espera". El empleado deberá aceptarla desde el sistema.');
    }

    /* =====================================================
        ACEPTAR ASIGNACIÓN (desde el sistema)
    ===================================================== */
    public function aceptar($id)
    {
        $asignacion = AsignacionMovil::with('dispositivo')->findOrFail($id);
        
        // Verificar que la asignación esté pendiente
        if ($asignacion->estado_asignacion !== 'pendiente') {
            return back()->with('error', 'Esta asignación ya fue respondida.');
        }
        
        // Verificar que el dispositivo está pendiente
        if ($asignacion->dispositivo->estado !== 'Pendiente') {
            return back()->with('error', 'El dispositivo no está en estado pendiente.');
        }
        
        // Verificar que el usuario autenticado es el empleado asignado
        $user = Auth::user();
        if ($user->numero_empleado != $asignacion->empleado_id) {
            return back()->with('error', 'No tienes permiso para aceptar esta asignación.');
        }
        
        // Actualizar asignación
        $asignacion->update([
            'estado_asignacion' => 'aceptada',
            'fecha_respuesta' => now(),
        ]);
        
        // Cambiar estado del dispositivo a "Asignado"
        $asignacion->dispositivo->update([
            'estado' => 'Asignado',
            'asignado' => true
        ]);

        // Notificar a los administradores
        Notification::send(
            User::where('role', 'admin')->get(),
            new SistemaNotificacion(
                'Asignación aceptada',
                ($asignacion->empleado->nombre_completo ?? 'Un empleado') . " aceptó el dispositivo {$asignacion->dispositivo->codigo_interno}.",
                route('asignaciones.moviles.dashboard'),
                'check-circle',
                'success'
            )
        );

        return redirect()->route('dashboard')
            ->with('success', ' Has aceptado el dispositivo ' . $asignacion->dispositivo->codigo_interno);
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

        $asignacion = AsignacionMovil::with(['dispositivo', 'empleado'])->findOrFail($id);

        if ($asignacion->estado_asignacion !== 'pendiente') {
            return back()->with('error', 'Esta asignación ya fue respondida.');
        }

        $user = Auth::user();
        if ($user->numero_empleado != $asignacion->empleado_id) {
            return back()->with('error', 'No tienes permiso para firmar esta asignación.');
        }

        // Guardar firma + aceptar
        $asignacion->update([
            'estado_asignacion' => 'aceptada',
            'fecha_respuesta'   => now(),
            'firma'             => $request->firma,
            'fecha_firma'       => now(),
        ]);

        $asignacion->dispositivo->update([
            'estado'   => 'Asignado',
            'asignado' => true,
        ]);

        // Regenerar la carta responsiva CON la firma incrustada
        try {
            if (!Storage::disk('public')->exists('cartas')) {
                Storage::disk('public')->makeDirectory('cartas');
            }
            $pdf = Pdf::loadView('pdf.carta_asignacion_movil', [
                'movil'      => $asignacion->dispositivo,
                'empleado'   => $asignacion->empleado,
                'asignacion' => $asignacion,
            ]);
            $ruta = 'cartas/carta_movil_' . $asignacion->id . '.pdf';
            Storage::disk('public')->put($ruta, $pdf->output());
            $asignacion->update(['carta_pdf' => $ruta]);
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF móvil firmado: ' . $e->getMessage());
        }

        // Notificar a los administradores
        Notification::send(
            User::where('role', 'admin')->get(),
            new SistemaNotificacion(
                'Asignación firmada',
                ($asignacion->empleado->nombre_completo ?? 'Un empleado') . " firmó y aceptó el dispositivo {$asignacion->dispositivo->codigo_interno}.",
                route('asignaciones.moviles.dashboard'),
                'check-circle',
                'success'
            )
        );

        return redirect()->route('dashboard')
            ->with('success', ' Firmaste y aceptaste el dispositivo ' . $asignacion->dispositivo->codigo_interno . '. Ya puedes descargar tu carta responsiva.');
    }

    /* =====================================================
        RECHAZAR ASIGNACIÓN (desde el sistema)
    ===================================================== */
    public function rechazar($id)
    {
        $asignacion = AsignacionMovil::with('dispositivo')->findOrFail($id);
        
        // Verificar que la asignación esté pendiente
        if ($asignacion->estado_asignacion !== 'pendiente') {
            return back()->with('error', 'Esta asignación ya fue respondida.');
        }
        
        // Verificar que el usuario autenticado es el empleado asignado
        $user = Auth::user();
        if ($user->numero_empleado != $asignacion->empleado_id) {
            return back()->with('error', 'No tienes permiso para rechazar esta asignación.');
        }
        
        // Actualizar asignación
        $asignacion->update([
            'estado_asignacion' => 'rechazada',
            'fecha_respuesta' => now(),
            'fecha_devolucion' => now(),
        ]);
        
        // Volver a Disponible cuando rechaza
        $asignacion->dispositivo->update([
            'estado' => 'Disponible',
            'asignado' => false
        ]);

        // Notificar a los administradores
        Notification::send(
            User::where('role', 'admin')->get(),
            new SistemaNotificacion(
                'Asignación rechazada',
                ($asignacion->empleado->nombre_completo ?? 'Un empleado') . " rechazó el dispositivo {$asignacion->dispositivo->codigo_interno}.",
                route('asignaciones.moviles.dashboard'),
                'x-circle',
                'warning'
            )
        );

        return redirect()->route('dashboard')
            ->with('info', ' Has rechazado el dispositivo ' . $asignacion->dispositivo->codigo_interno);
    }

    /* =====================================================
        DEVOLVER DISPOSITIVO (solo asignaciones ACEPTADAS)
    ===================================================== */
    public function devolver($id)
    {
        $asignacion = AsignacionMovil::with('dispositivo')->findOrFail($id);

        if ($asignacion->fecha_devolucion) {
            return back()->with('error', 'Este dispositivo ya fue devuelto.');
        }
        
        if ($asignacion->estado_asignacion !== 'aceptada') {
            return back()->with('error', 'Solo se pueden devolver asignaciones aceptadas.');
        }

        $asignacion->update([
            'fecha_devolucion' => now(),
        ]);

        $asignacion->dispositivo->update([
            'estado' => 'Disponible',
            'asignado' => false
        ]);

        return back()->with('success', ' Dispositivo devuelto correctamente.');
    }

    /* =====================================================
        VER PDF (CARTA RESPONSIVA)
    ===================================================== */
    public function responsiva($id)
    {
        $asignacion = AsignacionMovil::with(['empleado', 'dispositivo'])
            ->where('estado_asignacion', 'aceptada')
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.carta_asignacion_movil', [
            'movil' => $asignacion->dispositivo,
            'empleado' => $asignacion->empleado,
            'asignacion' => $asignacion
        ]);

        return $pdf->stream('carta_responsiva_movil_' . $asignacion->id . '.pdf');
    }

    /* =====================================================
        DESCARGAR PDF
    ===================================================== */
    public function descargar($id)
    {
        $asignacion = AsignacionMovil::findOrFail($id);

        // Solo admin o el empleado dueño de la asignación
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->numero_empleado != $asignacion->empleado_id) {
            abort(403, 'No tienes permiso para descargar esta carta.');
        }

        if (!$asignacion->carta_pdf) {
            return back()->with('error', 'No existe PDF. La carta se genera al firmar la asignación.');
        }

        if (!Storage::disk('public')->exists($asignacion->carta_pdf)) {
            return back()->with('error', 'El archivo PDF no existe.');
        }

        return Storage::disk('public')->download($asignacion->carta_pdf);
    }

    /* =====================================================
        ELIMINAR ASIGNACIÓN (solo rechazadas o devueltas)
    ===================================================== */
    public function destroy($id)
    {
        $asignacion = AsignacionMovil::findOrFail($id);

        if (!$asignacion->fecha_devolucion && $asignacion->estado_asignacion === 'aceptada') {
            return back()->with('error', 'No se puede eliminar una asignación activa.');
        }

        if ($asignacion->carta_pdf) {
            Storage::disk('public')->delete($asignacion->carta_pdf);
        }

        $asignacion->delete();

        return back()->with('success', ' Asignación eliminada correctamente.');
    }
}