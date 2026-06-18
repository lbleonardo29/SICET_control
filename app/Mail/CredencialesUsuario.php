<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public $empleado;
    public $password;

    public function __construct($empleado, $password)
    {
        $this->empleado = $empleado;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Tus credenciales de acceso a SICET')
                    ->view('emails.credenciales');
    }
}