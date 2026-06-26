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

    // ================= CAMBIO DE CONTRASEÑA (PRIMER INICIO) =================
    public function cambiarPasswordForm()
    {
        return view('auth.cambiar_password');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->primer_inicio = 0;
        $user->save();

        return redirect()->route('dashboard')->with('success', ' Contraseña cambiada correctamente.');
    }

    public function eliminarFoto()
{
    $user = Auth::user();
    
    if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
        Storage::disk('public')->delete($user->profile_photo);
        $user->profile_photo = null;
        $user->save();
    }
    
    return redirect()->route('perfil.index')->with('success', ' Foto eliminada correctamente');
}
}