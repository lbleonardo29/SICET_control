<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// MODELOS
use App\Models\Equipo;
use App\Models\Empleado;
use App\Models\Asignacion;
use App\Models\AsignacionMovil;
use App\Models\DispositivoMovil;

class AdminController extends Controller
{

    // =========================
    // LOGIN
    // =========================

    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        // Validación
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $login = $request->input('email');
        $password = $request->input('password');

        // Si el usuario escribe número → buscar por empleado_id
        if (is_numeric($login)) {
            $credentials = [
                'empleado_id' => $login,
                'password' => $password
            ];
        } else {
            // Si escribe texto → buscar por email
            $credentials = [
                'email' => $login,
                'password' => $password
            ];
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // ✅ Verificar si es primer inicio
            if ($user->primer_inicio == 1) {
                return redirect()->route('cambiar.password.form');
            }
            
            $request->session()->regenerate();
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no son correctas',
        ])->withInput();
    }

    // =========================
    // LOGOUT
    // =========================

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // =========================
    // DASHBOARD
    // =========================

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login')->withErrors(['error' => 'Sesión no iniciada']);
        }

        // =========================
        // COMPUTADORAS ACTIVAS DEL USUARIO (ACEPTADAS Y SIN DEVOLUCIÓN)
        // =========================
        $maquinas = Asignacion::with('equipo')
            ->where('empleado_id', $user->empleado_id)
            ->where('estado_asignacion', 'aceptada')
            ->whereNull('fecha_devolucion')
            ->get();

        // =========================
        // MÓVIL ACTIVO DEL USUARIO (ACEPTADO Y SIN DEVOLUCIÓN)
        // =========================
        $movil = AsignacionMovil::with('dispositivo')
            ->where('empleado_id', $user->empleado_id)
            ->where('estado_asignacion', 'aceptada')
            ->whereNull('fecha_devolucion')
            ->first();

        // =========================
        // ASIGNACIONES PENDIENTES (solo para usuarios normales y seguridad)
        // =========================
        $asignacionesPendientes = collect();
        $movilesPendientes = collect();

        if (in_array($user->role, ['user', 'usuario', 'seguridad'])) {
            // Computadoras pendientes
            $asignacionesPendientes = Asignacion::with('equipo')
                ->where('empleado_id', $user->empleado_id)
                ->where('estado_asignacion', 'pendiente')
                ->get();

            // Móviles pendientes
            $movilesPendientes = AsignacionMovil::with('dispositivo')
                ->where('empleado_id', $user->empleado_id)
                ->where('estado_asignacion', 'pendiente')
                ->get();
        }

        // =========================
        // DATOS SOLO ADMIN
        // =========================
        if ($user->role === 'admin') {
            $totalEquipos = Equipo::count();
            $equiposDisponibles = Equipo::where('estado', 'Disponible')->count();
            $equiposAsignados = Asignacion::where('estado_asignacion', 'aceptada')
                ->whereNull('fecha_devolucion')
                ->count();

            $totalMoviles = DispositivoMovil::count();
            $movilesDisponibles = DispositivoMovil::where('estado', 'Disponible')->count();
            $movilesAsignados = AsignacionMovil::where('estado_asignacion', 'aceptada')
                ->whereNull('fecha_devolucion')
                ->count();

            $totalEmpleados = Empleado::count();

            // ✅ CORREGIDO: usa 'admin.dashboard' con 'maquinas' (plural)
            return view('admin.dashboard', compact(
                'user',
                'maquinas',
                'movil',
                'asignacionesPendientes',
                'movilesPendientes',
                'totalEquipos',
                'equiposDisponibles',
                'equiposAsignados',
                'totalMoviles',
                'movilesDisponibles',
                'movilesAsignados',
                'totalEmpleados'
            ));
        }

        // =========================
        // DASHBOARD USUARIO NORMAL Y SEGURIDAD
        // =========================
        // ✅ CORREGIDO: usa 'admin.dashboard' con 'maquinas' (plural)
        return view('admin.dashboard', compact(
            'user',
            'maquinas',
            'movil',
            'asignacionesPendientes',
            'movilesPendientes'
        ));
    }
}