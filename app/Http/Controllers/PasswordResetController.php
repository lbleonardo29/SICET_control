<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\CredencialesUsuario;

class PasswordResetController extends Controller
{
    // Mostrar formulario para solicitar reset
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Generar una contraseña temporal y enviarla por correo
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'correo' => 'required|string'
        ]);

        $input = trim($request->correo);

        // Aceptar correo O número de empleado
        $user = User::where('email', $input)->first();
        if (!$user && is_numeric($input)) {
            $user = User::where('numero_empleado', $input)->first();
        }

        // Siempre devolver mensaje genérico para no revelar si el dato existe
        if (!$user) {
            return back()->with('success', 'Si tu correo está registrado, recibirás una contraseña temporal en breve.');
        }

        // Generar contraseña temporal y forzar cambio en el próximo inicio
        $temporal = User::generarPasswordTemporal();
        $user->password = Hash::make($temporal);
        $user->primer_inicio = 1;
        $user->save();

        try {
            $datos = (object) ['nombre_completo' => $user->name];
            Mail::to($user->email)->send(new CredencialesUsuario($datos, $temporal));
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo de recuperación: ' . $e->getMessage());
        }

        return back()->with('success', 'Si tu correo está registrado, recibirás una contraseña temporal en breve.');
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