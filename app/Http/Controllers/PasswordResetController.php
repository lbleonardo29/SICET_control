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
            'correo' => 'required|email'
        ]);

        $user = User::where('email', $request->correo)->first();

        // Siempre devolver mensaje genérico para no revelar si el correo existe
        if (!$user) {
            return back()->with('success', 'Si tu correo está registrado, recibirás un enlace en breve.');
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = route('password.reset', $token);

        try {
            Mail::send('emails.password-reset', [
                'nombre' => $user->name,
                'resetUrl' => $resetUrl,
                'email' => $user->email,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Recuperación de contraseña - SICET');
            });
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo de recuperación: ' . $e->getMessage());
        }

        return back()->with('success', 'Si tu correo está registrado, recibirás un enlace en breve.');
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