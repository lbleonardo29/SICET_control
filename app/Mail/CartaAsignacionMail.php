<?php

namespace App\Mail;

use App\Models\Asignacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CartaAsignacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $asignacion;

    public function __construct(Asignacion $asignacion)
    {
        $this->asignacion = $asignacion;
    }

public function build()
{
    $path = $this->asignacion->carta_pdf ?? '';

    // Previene path traversal: solo se permiten rutas dentro de cartas/
    if (!str_starts_with($path, 'cartas/')) {
        throw new \InvalidArgumentException('Ruta de carta PDF inválida.');
    }

    return $this->subject('Carta de Asignación de Equipo')
        ->view('emails.carta_asignacion')
        ->attach(storage_path('app/public/' . $path));
}

}
