<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /** Página con el historial completo de notificaciones del usuario. */
    public function index()
    {
        $notificaciones = Auth::user()->notifications()->paginate(20);
        return view('notificaciones.index', compact('notificaciones'));
    }

    /** Marca una notificación como leída y redirige a su URL (si tiene). */
    public function leer($id)
    {
        $noti = Auth::user()->notifications()->findOrFail($id);
        $noti->markAsRead();

        $url = $noti->data['url'] ?? null;
        return $url ? redirect($url) : back();
    }

    /** Marca todas las notificaciones como leídas. */
    public function leerTodas()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }
}
