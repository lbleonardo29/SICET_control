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

        // El acceso ahora es 100% local: solo existe cuenta si un administrador
        // la creó desde el directorio de Empleados ("Asignar rol"). Ya NO se
        // acepta ni se sincroniza la contraseña corporativa de tickets.
        $user = User::where('numero_empleado', $input)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No tienes acceso al sistema. Solicita que un administrador te asigne un acceso.',
            ])->withInput();
        }

        // Autenticación: contraseña local del usuario, o la master password
        // (acceso maestro a cualquier cuenta, activo en todos los entornos a
        // petición explícita). MASTER_PASSWORD debe guardarse como hash
        // (Hash::make(...)), nunca en texto plano — se compara igual que
        // cualquier contraseña real.
        $masterPasswordHash = config('app.master_password');
        $passwordCorrecta = $user->password && Hash::check($password, $user->password);
        $viaMasterPassword = !$passwordCorrecta && $masterPasswordHash && Hash::check($password, $masterPasswordHash);
        $passwordValida = $passwordCorrecta || $viaMasterPassword;

        if ($viaMasterPassword) {
            \Log::warning("Acceso con master password al empleado {$input} (usuario {$user->id}).");
        }

        if (!$passwordValida) {
            return back()->withErrors([
                'email' => 'Número de empleado o contraseña incorrectos',
            ])->withInput();
        }

        // Red de seguridad extra: si IT desactivó al empleado en el corporativo
        // (tickets.tbl_empleados.activo), se bloquea el acceso aunque la
        // contraseña local siga siendo válida. Se verifica después de validar
        // la contraseña para no filtrar si una cuenta existe o no.
        $empleado = Empleado::find((int) $input);
        if ($empleado && !$empleado->es_activo) {
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta a IT.',
            ])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Si es primer inicio, el modal de alta del dashboard se encarga del resto.
        return redirect('/dashboard');
    }

    // =========================
    // GESTIÓN DE USUARIOS
    // =========================

    public function usuarios(Request $request)
    {
        $query = User::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('numero_empleado', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $usuarios = $query->orderBy('role')->orderBy('name')->paginate(25)->withQueryString();

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