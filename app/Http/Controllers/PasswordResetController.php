<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class PasswordResetController extends Controller
{
    // Mostrar formulario para solicitar reset
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Enviar enlace de recuperación por correo
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'numero_empleado' => 'required'
        ]);

        // Buscar en tbl_empleados por número de empleado
        $empleado = DB::table('tbl_empleados')
            ->where('numero_empleado', $request->numero_empleado)
            ->where('activo', 'S')
            ->first();

        if (!$empleado) {
            return back()->withErrors(['numero_empleado' => 'Número de empleado no encontrado']);
        }

        // Buscar usuario por email
        $user = User::where('email', $empleado->email)->first();

        if (!$user) {
            return back()->withErrors(['numero_empleado' => 'No hay un usuario asociado a este número de empleado']);
        }

        // Generar token único
        $token = Str::random(60);
        
        // Guardar token en la tabla
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // ENVIAR CORREO A TU CORREO PERSONAL (PARA PRUEBAS)
        // Cambia 'tu_correo_personal@gmail.com' por tu correo
        $correoPrueba = 'rogatwin09@gmail.com'; // 👈 Tu correo personal
        
        $nombreEmpleado = $empleado->nombre . ' ' . $empleado->apellidos;
        $resetUrl = route('password.reset', $token);

        try {
            Mail::send('emails.password-reset', [
                'nombre' => $nombreEmpleado,
                'resetUrl' => $resetUrl,
                'email' => $user->email,
                'empleado' => $empleado
            ], function($message) use ($correoPrueba) {
                $message->to($correoPrueba)
                        ->subject('PRUEBA - Recuperación de contraseña - SICET');
            });
            
            return back()->with('success', 'Enlace de recuperación enviado a tu correo personal (rogatwin09@gmail.com) para pruebas.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error al enviar correo: ' . $e->getMessage()]);
        }
    }

    // Mostrar formulario para nueva contraseña
    public function showResetForm($token)
    {
        $resetRecord = DB::table('password_reset_tokens')->where('token', $token)->first();
        
        if (!$resetRecord) {
            return redirect()->route('password.request')
                ->withErrors(['token' => 'El enlace es inválido. Solicita uno nuevo.']);
        }
        
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->diffInHours(now()) > 24) {
            return redirect()->route('password.request')
                ->withErrors(['token' => 'El enlace ha expirado. Solicita uno nuevo.']);
        }
        
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $resetRecord->email
        ]);
    }

    // Guardar nueva contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'El enlace es inválido. Solicita uno nuevo.']);
        }

        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->diffInHours(now()) > 24) {
            return back()->withErrors(['email' => 'El enlace ha expirado. Solicita uno nuevo.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Contraseña cambiada correctamente. Ahora puedes iniciar sesión con tu nueva contraseña.');
    }
}