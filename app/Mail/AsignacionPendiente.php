<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AsignacionPendiente extends Mailable
{
    use Queueable, SerializesModels;

    public $asignacion;
    public $tipo;

    /**
     * Create a new message instance.
     *
     * @param mixed $asignacion  // Instancia de Asignacion o AsignacionMovil
     * @param string $tipo       // 'equipo' o 'movil'
     */
    public function __construct($asignacion, $tipo)
    {
        $this->asignacion = $asignacion;
        $this->tipo = $tipo;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $titulo = $this->tipo === 'equipo' ? 'Computadora' : 'Dispositivo Móvil';
        
        return $this->subject("📱 SICET - Nueva asignación pendiente de {$titulo}")
                    ->view('emails.asignacion_pendiente');
    }
}