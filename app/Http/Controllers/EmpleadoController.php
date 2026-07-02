<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Empleado;
use App\Models\User;
use App\Models\Planta;
use App\Mail\CredencialesUsuario;
use App\Notifications\SistemaNotificacion;

/**
 * Empleados = DIRECTORIO DE SOLO LECTURA.
 *
 * Los empleados son maestro del corporativo (tickets.tbl_empleados, solo
 * lectura). La tabla local `empleados` de SICET fue eliminada, por lo que ya no
 * se pueden dar de alta, editar, activar/desactivar ni eliminar desde SICET.
 *
 * El acceso a SICET ahora es 100% controlado por un administrador: se otorga
 * explícitamente vía asignarRol() (ya no hay autoregistro al iniciar sesión
 * con la contraseña corporativa).
 */
class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Listar empleados (directorio de consulta). Solo empleados ACTIVOS del
    // corporativo: los dados de baja no aportan al directorio de SICET.
    public function index(Request $request)
    {
        $query = Empleado::activos();

        if ($request->filled('q')) {
            $query->buscar($request->q);
        }

        $empleados = $query->orderBy('id_emp', 'asc')->get();

        // Vincular la cuenta de usuario (si existe) SIN eager-load cruzado de conexión:
        // el empleado vive en `tickets` y users en la BD local, así que se consulta aparte.
        $numeros = $empleados->pluck('id_emp')->map(fn ($n) => (string) $n)->all();
        $users = User::whereIn('numero_empleado', $numeros)->get()->keyBy('numero_empleado');
        $empleados->each(fn ($e) => $e->setRelation('user', $users->get((string) $e->id_emp)));

        // Igual para la planta (misma razón: Empleado no puede tener relaciones
        // hacia tablas locales; ver nota en app/Models/Empleado.php).
        $idsPlanta = $empleados->pluck('id_planta')->filter()->unique()->all();
        $plantas = Planta::whereIn('id_planta_corp', $idsPlanta)->get()->keyBy('id_planta_corp');
        $empleados->each(fn ($e) => $e->setRelation('planta', $plantas->get($e->id_planta)));

        return view('empleados.index', compact('empleados'));
    }

    /**
     * Da de alta la cuenta local (User) de un empleado corporativo que aún
     * no tiene acceso a SICET. Genera contraseña temporal, envía credenciales
     * por correo y fuerza el alta (primer_inicio) en el próximo login.
     */
    public function asignarRol(Request $request, $id_emp)
    {
        $request->validate([
            'role' => 'required|in:admin,rh,user',
        ]);

        $empleado = Empleado::find((int) $id_emp);

        if (!$empleado) {
            return back()->with('error', 'El empleado no existe en el directorio corporativo.');
        }

        // Guard principal: evitar doble-click / doble-submit antes de tocar la BD.
        if (User::where('numero_empleado', (string) $empleado->id_emp)->exists()) {
            return back()->with('error', "El empleado {$empleado->nombre_completo} ya tiene una cuenta en el sistema.");
        }

        $emailLocal = $empleado->correo ?? "{$empleado->id_emp}@sicet.fruitex.mx";
        $temporal   = User::generarPasswordTemporal();

        try {
            $user = User::create([
                'numero_empleado' => (string) $empleado->id_emp,
                'name'            => $empleado->nombre_completo,
                'email'           => $emailLocal,
                'password'        => Hash::make($temporal),
                'role'            => $request->role,
                'primer_inicio'   => 1,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Backstop del guard anterior: la restricción UNIQUE de numero_empleado
            // evita duplicados por condición de carrera (dos confirmaciones casi simultáneas).
            return back()->with('error', "El empleado {$empleado->nombre_completo} ya tiene una cuenta en el sistema.");
        }

        $correoEnviado = true;
        try {
            Mail::to($user->email)->send(new CredencialesUsuario($empleado, $temporal));
        } catch (\Exception $e) {
            $correoEnviado = false;
            \Log::error('Error al enviar correo de credenciales (asignarRol): ' . $e->getMessage());
        }

        // Notificación interna (campana): avisa al nuevo usuario que ya tiene
        // acceso, igual que se hace al recibir una asignación de equipo/móvil.
        $user->notify(new SistemaNotificacion(
            'Bienvenido a SICET',
            "Se te otorgó acceso con rol \"{$request->role}\". Revisa tu correo ({$user->email}) para tu contraseña temporal.",
            route('dashboard'),
            'stars',
            'success'
        ));

        if ($correoEnviado) {
            $mensaje = "Cuenta creada para {$empleado->nombre_completo} ({$request->role}). Se enviaron las credenciales a {$user->email}.";
        } else {
            $mensaje = "Cuenta creada para {$empleado->nombre_completo} ({$request->role}), pero el correo con las credenciales NO pudo enviarse a {$user->email}. Revisa manualmente o usa \"Olvidé mi contraseña\" para reenviarle una temporal.";
        }

        return redirect()->route('empleados.index')
            ->with($correoEnviado ? 'success' : 'error', $mensaje);
    }
}
