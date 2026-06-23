<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// MODELOS
use App\Models\User;
use App\Models\Equipo;
use App\Models\Empleado;
use App\Models\Asignacion;
use App\Models\AsignacionMovil;
use App\Models\DispositivoMovil;
use App\Models\Corp\EmpleadoTicket;

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
        $request->validate([
            'email'    => 'required',
            'password' => 'required',
        ]);

        $input    = trim($request->input('email'));
        $password = $request->input('password');

        // Solo se acepta número de empleado (id_emp numérico)
        if (!is_numeric($input)) {
            return back()->withErrors([
                'email' => 'Ingresa tu número de empleado (solo números)',
            ])->withInput();
        }

        // Buscar en tickets.tbl_empleados (solo lectura)
        $empleado = EmpleadoTicket::find((int) $input);

        if (!$empleado) {
            return back()->withErrors([
                'email' => 'Número de empleado o contraseña incorrectos',
            ])->withInput();
        }

        // Verificar contraseña: hash real O master password (solo en entorno local)
        $masterPassword = config('app.env') === 'local' ? env('MASTER_PASSWORD') : null;
        $passwordValida = Hash::check($password, $empleado->contrasenia)
            || ($masterPassword && $password === $masterPassword);

        if (!$passwordValida) {
            return back()->withErrors([
                'email' => 'Número de empleado o contraseña incorrectos',
            ])->withInput();
        }

        // Verificar que esté activo (puede ser 1, '1', 'S', 's')
        if (!in_array($empleado->activo, [1, '1', 'S', 's', true], true)) {
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta a IT.',
            ])->withInput();
        }

        $nombreCompleto = trim("{$empleado->nombre} {$empleado->apellidos}");
        $emailLocal     = $empleado->email ?? "{$empleado->id_emp}@sicet.fruitex.mx";

        // Buscar o crear User local en sicet.users vinculado por numero_empleado
        $user = User::firstOrCreate(
            ['numero_empleado' => (string) $empleado->id_emp],
            [
                'name'          => $nombreCompleto,
                'email'         => $emailLocal,
                'password'      => $empleado->contrasenia,
                'role'          => 'user',
                'primer_inicio' => 0,
            ]
        );

        // Sincronizar nombre y hash en cada login (por si cambiaron en tickets)
        $user->name     = $nombreCompleto;
        $user->password = $empleado->contrasenia;
        $user->save();

        Auth::login($user, $request->boolean('remember'));

        if ($user->primer_inicio == 1) {
            return redirect()->route('cambiar.password.form');
        }

        $request->session()->regenerate();
        return redirect('/dashboard');
    }

    // =========================
    // GESTIÓN DE USUARIOS
    // =========================

    public function usuarios()
    {
        $usuarios = User::orderBy('role')->orderBy('name')->paginate(25);
        return view('admin.usuarios', compact('usuarios'));
    }

    public function actualizarRol(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,seguridad,user',
        ]);

        $user = User::findOrFail($id);

        // No permitir quitarse el rol admin a uno mismo
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'No puedes cambiar tu propio rol de administrador.');
        }

        $user->role = $request->role;
        $user->save();

        return back()->with('success', "Rol de {$user->name} actualizado a {$request->role}.");
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

            // CORREGIDO: usa 'admin.dashboard' con 'maquinas' (plural)
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
        // CORREGIDO: usa 'admin.dashboard' con 'maquinas' (plural)
        return view('admin.dashboard', compact(
            'user',
            'maquinas',
            'movil',
            'asignacionesPendientes',
            'movilesPendientes'
        ));
    }
}