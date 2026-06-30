<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return view('perfil.index');
    }

    public function update(Request $request)
    {
        $request->validate([
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_photo')) {

            // borrar anterior
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')
                ->store('profiles', 'public');

            $user->profile_photo = $path;
            $user->save();
        }

        return back()->with('success', 'Perfil actualizado correctamente');
    }

    // ================= ALTA / CAMBIO DE CONTRASEÑA (PRIMER INICIO) =================
    // El formulario dedicado quedó reemplazado por el modal del dashboard.
    public function cambiarPasswordForm()
    {
        return redirect()->route('dashboard');
    }

    public function cambiarPassword(Request $request)
    {
        $user = Auth::user();

        // Si aún no se ha dado de alta (sin firma), exigir también la firma.
        $requiereFirma = empty($user->firma);

        $reglas = ['password' => 'required|min:8|confirmed'];
        if ($requiereFirma) {
            $reglas['firma'] = 'required|string';
        }
        $request->validate($reglas, [
            'firma.required' => 'Debes firmar para darte de alta en la plataforma.',
        ]);

        if ($requiereFirma) {
            $user->firma = $request->firma;          // PNG en base64 (data URI)
            $user->firma_alta_at = now();
        }

        $user->password = Hash::make($request->password);
        $user->primer_inicio = 0;
        $user->save();

        return redirect()->route('dashboard')->with('success', ' Listo, tu cuenta quedó activada.');
    }

    public function eliminarFoto()
{
    $user = Auth::user();
    
    if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
        Storage::disk('public')->delete($user->profile_photo);
        $user->profile_photo = null;
        $user->save();
    }
    
    return redirect()->route('perfil.index')->with('success', 'Foto eliminada correctamente');
}
}