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
    return $this->subject('Carta de Asignación de Equipo')
        ->view('emails.carta_asignacion')
        ->attach(
            storage_path('app/public/' . $this->asignacion->carta_pdf)
        );
}

}
