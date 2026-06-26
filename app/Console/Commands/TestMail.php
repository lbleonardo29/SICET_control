<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Envía un correo de prueba para verificar la configuración SMTP del .env.
 * Uso:  php artisan sicet:test-mail correo-de-prueba@gmail.com
 */
class TestMail extends Command
{
    protected $signature = 'sicet:test-mail {correo : Dirección de destino del correo de prueba}';

    protected $description = 'Envía un correo de prueba a la dirección indicada para verificar el SMTP.';

    public function handle(): int
    {
        $correo = $this->argument('correo');

        $this->info("Remitente:  " . config('mail.from.address') . " (" . config('mail.from.name') . ")");
        $this->info("Servidor:   " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port'));
        $this->info("Enviando a: {$correo} ...");

        try {
            Mail::raw(
                "Correo de prueba de SICET.\n\n" .
                "Si recibes este mensaje, la configuración de correo funciona correctamente.\n\n" .
                "— Sistema de Control de Equipos · Fruitex de México",
                function ($m) use ($correo) {
                    $m->to($correo)->subject('Prueba de correo · SICET');
                }
            );

            $this->newLine();
            $this->info('✔ Correo enviado. Revisa la bandeja de entrada (y la carpeta de spam).');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('✗ Error al enviar: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
