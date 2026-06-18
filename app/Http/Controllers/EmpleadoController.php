<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Empleado;
use App\Models\User;
use App\Mail\CredencialesUsuario;

class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['index']);
    }

    // 📋 Listar empleados
    public function index()
    {
        $empleados = Empleado::with('user')->orderBy('id_emp', 'desc')->get();
        return view('empleados.index', compact('empleados'));
    }

    // ➕ Formulario crear
    public function create()
    {
        return view('empleados.create');
    }

    // 💾 Guardar SOLO EMPLEADO
    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email'     => 'required|email|unique:tbl_empleados,email',
        ]);

        Empleado::create([
            'nombre'    => $request->nombre,
            'apellidos' => $request->apellidos,
            'email'     => $request->email,
            'activo'    => 1,
        ]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado. Aún no tiene acceso al sistema.');
    }

    // ✏️ Editar empleado
    public function edit($id)
    {
        $empleado = Empleado::with('user')->where('id_emp', $id)->firstOrFail();
        return view('empleados.edit', compact('empleado'));
    }

    // 🔄 Actualizar empleado y crear usuario si hay rol
    public function update(Request $request, $id)
    {
        $empleado = Empleado::with('user')->where('id_emp', $id)->firstOrFail();

        $request->validate([
            'nombre'    => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . optional($empleado->user)->id,
            'role'      => 'nullable|in:admin,user,seguridad',
            'activo'    => 'required|boolean',
        ]);

        $empleado->update([
            'nombre'    => $request->nombre,
            'apellidos' => $request->apellidos,
            'email'     => $request->email,
            'activo'    => $request->activo,
        ]);

        if ($request->role) {
            $user = User::where('empleado_id', $empleado->id_emp)->first();

            if (!$user) {
                $passwordTemporal = User::generarPasswordTemporal();

                User::create([
                    'name'          => $empleado->nombre_completo,
                    'email'         => $empleado->email,
                    'empleado_id'   => $empleado->id_emp,
                    'password'      => Hash::make($passwordTemporal),
                    'role'          => $request->role,
                    'primer_inicio' => 1,
                ]);

                try {
                    Mail::to('rogatwin09@gmail.com')->send(new CredencialesUsuario($empleado, $passwordTemporal));
                } catch (\Exception $e) {
                    \Log::error('Error al enviar correo: ' . $e->getMessage());
                }

                return redirect()->route('empleados.index')
                    ->with('success', '✅ Usuario creado correctamente.');
            }

            $user->update([
                'name'  => $empleado->nombre_completo,
                'email' => $empleado->email,
                'role'  => $request->role,
            ]);
        }

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    // 🗑️ Eliminar empleado y usuario
    public function destroy($id)
    {
        $empleado = Empleado::with('user')->where('id_emp', $id)->firstOrFail();

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
        $empleado = Empleado::where('id_emp', $id)->firstOrFail();

        $nuevoEstado = $empleado->activo ? 0 : 1;
        $empleado->update(['activo' => $nuevoEstado]);

        if ($empleado->user && $nuevoEstado == 0) {
            $passwordTemporal = User::generarPasswordTemporal();
            $empleado->user->update([
                'password' => Hash::make($passwordTemporal),
                'primer_inicio' => 1,
            ]);
        }

        return redirect()->route('empleados.index')
            ->with('success', $nuevoEstado ? '✅ Empleado activado correctamente' : '✅ Empleado desactivado correctamente');
    }
}