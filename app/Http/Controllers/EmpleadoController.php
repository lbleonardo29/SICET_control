<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Empleado;
use App\Models\User;
use App\Models\Planta;
use App\Mail\CredencialesUsuario;
use App\Services\TicketsEmpleadoService;

class EmpleadoController extends Controller
{
    public function __construct(protected TicketsEmpleadoService $tickets)
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['index']);
    }

    /**
     * Ejecuta una escritura hacia el corporativo de forma best-effort (local-first).
     * Devuelve '' si todo bien (o si el write-through está deshabilitado) y una nota
     * de aviso si falló, para anexarla al mensaje de éxito.
     */
    private function syncCorp(callable $accion): string
    {
        if (!$this->tickets->enabled()) {
            return '';
        }

        try {
            $accion();
            return '';
        } catch (\Throwable $e) {
            \Log::error('Write-through tickets falló: ' . $e->getMessage());
            return ' ⚠️ No se pudo sincronizar con el sistema corporativo; se reintentará en la próxima sincronización.';
        }
    }

    // 📋 Listar empleados
    public function index()
    {
        $empleados = Empleado::with('user')->orderBy('id', 'desc')->get();
        return view('empleados.index', compact('empleados'));
    }

    // ➕ Formulario crear
    public function create()
    {
        $plantas = Planta::orderBy('nombre')->get();
        return view('empleados.create', compact('plantas'));
    }

    // 💾 Guardar empleado (y usuario de acceso si se asigna rol)
    public function store(Request $request)
    {
        $request->validate([
            'numero_empleado' => 'required|string|max:20|unique:empleados,numero_empleado',
            'nombre'          => 'required|string|max:255',
            'email'           => 'required|email|unique:empleados,correo',
            'planta_id'       => 'required|exists:plantas,id',
            'role'            => 'nullable|in:admin,user,seguridad',
            'password'        => 'nullable|min:8',
            'activo'          => 'nullable|boolean',
        ]);

        $empleado = Empleado::create([
            'numero_empleado' => $request->numero_empleado,
            'nombre_completo' => $request->nombre,
            'correo'          => $request->email,
            'planta_id'       => $request->planta_id,
            'activo'          => $request->boolean('activo'),
        ]);

        // Write-through al corporativo (best-effort; no bloquea si tickets no está disponible).
        $nota = $this->syncCorp(fn () => $this->tickets->pushUpsert($empleado));

        // Si se asignó un rol, crear el usuario de acceso al sistema
        if ($request->role) {
            $password = $request->filled('password')
                ? $request->password
                : User::generarPasswordTemporal();

            User::create([
                'name'            => $empleado->nombre_completo,
                'email'           => $empleado->correo,
                'numero_empleado' => $empleado->numero_empleado,
                'empleado_id'     => $empleado->id,
                'password'        => Hash::make($password),
                'role'            => $request->role,
                'primer_inicio'   => 1,
            ]);

            try {
                // TODO (fase seguridad): enviar a $empleado->correo en lugar del correo de pruebas
                Mail::to('rogatwin09@gmail.com')->send(new CredencialesUsuario($empleado, $password));
            } catch (\Exception $e) {
                \Log::error('Error al enviar correo: ' . $e->getMessage());
            }

            return redirect()->route('empleados.index')
                ->with('success', '✅ Empleado y usuario creados correctamente.' . $nota);
        }

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado. Aún no tiene acceso al sistema.' . $nota);
    }

    // ✏️ Editar empleado
    public function edit($id)
    {
        $empleado = Empleado::with('user')->where('id', $id)->firstOrFail();
        $plantas = Planta::orderBy('nombre')->get();
        return view('empleados.edit', compact('empleado', 'plantas'));
    }

    // 🔄 Actualizar empleado y crear usuario si hay rol
    public function update(Request $request, $id)
    {
        $empleado = Empleado::with('user')->where('id', $id)->firstOrFail();

        $request->validate([
            'nombre'    => 'required|string|max:255',
            'email'     => 'required|email|unique:empleados,correo,' . $empleado->id,
            'planta_id' => 'required|exists:plantas,id',
            'role'      => 'nullable|in:admin,user,seguridad',
            'activo'    => 'required|boolean',
        ]);

        $empleado->update([
            'nombre_completo' => $request->nombre,
            'correo'          => $request->email,
            'planta_id'       => $request->planta_id,
            'activo'          => $request->activo,
        ]);

        // Write-through al corporativo (best-effort).
        $nota = $this->syncCorp(fn () => $this->tickets->pushUpsert($empleado));

        if ($request->role) {
            $user = User::where('empleado_id', $empleado->id)->first();

            if (!$user) {
                $passwordTemporal = User::generarPasswordTemporal();

                User::create([
                    'name'            => $empleado->nombre_completo,
                    'email'           => $empleado->correo,
                    'numero_empleado' => $empleado->numero_empleado,
                    'empleado_id'     => $empleado->id,
                    'password'        => Hash::make($passwordTemporal),
                    'role'            => $request->role,
                    'primer_inicio'   => 1,
                ]);

                try {
                    Mail::to('rogatwin09@gmail.com')->send(new CredencialesUsuario($empleado, $passwordTemporal));
                } catch (\Exception $e) {
                    \Log::error('Error al enviar correo: ' . $e->getMessage());
                }

                return redirect()->route('empleados.index')
                    ->with('success', '✅ Usuario creado correctamente.' . $nota);
            }

            $user->update([
                'name'  => $empleado->nombre_completo,
                'email' => $empleado->correo,
                'role'  => $request->role,
            ]);
        }

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado correctamente.' . $nota);
    }

    // 🗑️ Eliminar empleado (o darlo de baja si la integración está activa)
    public function destroy($id)
    {
        $empleado = Empleado::with('user')->where('id', $id)->firstOrFail();

        // Modo integrado: el corporativo es el maestro -> dar de baja (no borrar),
        // porque un borrado local reaparecería en la próxima sincronización.
        if ($this->tickets->enabled()) {
            $empleado->update(['activo' => 0]);

            if ($empleado->user) {
                $empleado->user->update([
                    'password' => Hash::make(User::generarPasswordTemporal()),
                    'primer_inicio' => 1,
                ]);
            }

            $nota = $this->syncCorp(fn () => $this->tickets->pushActivo($empleado->numero_empleado, false));

            return redirect()->route('empleados.index')
                ->with('success', 'Empleado dado de baja.' . $nota);
        }

        // Modo standalone: borrado local completo.
        if ($empleado->user) {
            $empleado->user->delete();
        }

        $empleado->delete();

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado eliminado correctamente');
    }

    // 🔁 Activar / desactivar empleado
    public function toggle($id)
    {
        $empleado = Empleado::where('id', $id)->firstOrFail();

        $nuevoEstado = $empleado->activo ? 0 : 1;
        $empleado->update(['activo' => $nuevoEstado]);

        // Write-through del estado activo al corporativo.
        $nota = $this->syncCorp(fn () => $this->tickets->pushActivo($empleado->numero_empleado, (bool) $nuevoEstado));

        if ($empleado->user && $nuevoEstado == 0) {
            $passwordTemporal = User::generarPasswordTemporal();
            $empleado->user->update([
                'password' => Hash::make($passwordTemporal),
                'primer_inicio' => 1,
            ]);
        }

        return redirect()->route('empleados.index')
            ->with('success', ($nuevoEstado ? '✅ Empleado activado correctamente' : '✅ Empleado desactivado correctamente') . $nota);
    }
}