<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notificación interna genérica de SICET (canal de base de datos).
 * Se muestra en la campana del header. Parametrizable para cualquier evento.
 */
class SistemaNotificacion extends Notification
{
    use Queueable;

    public function __construct(
        public string $titulo,
        public string $mensaje,
        public ?string $url = null,
        public string $icono = 'bell',
        public string $tipo = 'info'   // info | success | warning | danger
    ) {}

    /** Solo canal de base de datos (campana en la app). */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /** Carga que se guarda en notifications.data (JSON). */
    public function toArray($notifiable): array
    {
        return [
            'titulo'  => $this->titulo,
            'mensaje' => $this->mensaje,
            'url'     => $this->url,
            'icono'   => $this->icono,
            'tipo'    => $this->tipo,
        ];
    }
}
