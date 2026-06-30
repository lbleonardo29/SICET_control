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

        // Usuario local espejo (puede no existir aún en el primer inicio).
        // El empleado es maestro del corporativo (tickets); aquí solo guardamos la cuenta.
        $user = User::where('numero_empleado', (string) $empleado->id_emp)->first();

        // Autenticación HÍBRIDA: se acepta la contraseña corporativa O la contraseña
        // local del sistema (la que el usuario fijó en su alta / la temporal de
        // recuperación) O la master password (solo en entorno local).
        $masterPassword = config('app.env') === 'local' ? env('MASTER_PASSWORD') : null;
        $passwordValida = Hash::check($password, $empleado->contrasenia)
            || ($user && $user->password && Hash::check($password, $user->password))
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

        if (!$user) {
            // Primer inicio: se crea el espejo local y se fuerza el alta (firma + contraseña).
            $user = User::create([
                'numero_empleado' => (string) $empleado->id_emp,
                'name'            => $nombreCompleto,
                'email'           => $emailLocal,
                'password'        => $empleado->contrasenia, // espejo inicial del corporativo
                'role'            => 'user',
                'primer_inicio'   => 1,
            ]);
        } else {
            // En cada login solo se sincronizan datos NO sensibles.
            // La contraseña local NO se sobrescribe: si no, se perdería la que fijó el usuario.
            $user->name = $nombreCompleto;
            // Mantener el correo actualizado (para la recuperación), si no lo ocupa otro usuario.
            if ($emailLocal && $user->email !== $emailLocal) {
                $ocupado = User::where('email', $emailLocal)->where('id', '!=', $user->id)->exists();
                if (!$ocupado) {
                    $user->email = $emailLocal;
                }
            }
            $user->save();
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Si es primer inicio, el modal de alta del dashboard se encarga del resto.
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
            'role' => 'required|in:admin,rh,user',
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
        // El vínculo con asignaciones es por número de empleado (= id_emp corporativo).
        $numEmp = $user->numero_empleado;

        $maquinas = Asignacion::with('equipo')
            ->where('empleado_id', $numEmp)
            ->where('estado_asignacion', 'aceptada')
            ->whereNull('fecha_devolucion')
            ->get();

        // =========================
        // MÓVIL ACTIVO DEL USUARIO (ACEPTADO Y SIN DEVOLUCIÓN)
        // =========================
        $movil = AsignacionMovil::with('dispositivo')
            ->where('empleado_id', $numEmp)
            ->where('estado_asignacion', 'aceptada')
            ->whereNull('fecha_devolucion')
            ->first();

        // =========================
        // ASIGNACIONES PENDIENTES (cualquier rol con empleado vinculado:
        // todos pueden recibir equipo y deben poder aceptarlo/rechazarlo)
        // =========================
        $asignacionesPendientes = collect();
        $movilesPendientes = collect();

        if ($numEmp) {
            // Computadoras pendientes
            $asignacionesPendientes = Asignacion::with('equipo')
                ->where('empleado_id', $numEmp)
                ->where('estado_asignacion', 'pendiente')
                ->get();

            // Móviles pendientes
            $movilesPendientes = AsignacionMovil::with('dispositivo')
                ->where('empleado_id', $numEmp)
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

            $totalEmpleados = Empleado::activos()->count();

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