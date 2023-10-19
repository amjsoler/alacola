<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificarCuenta extends Notification
{
    use Queueable;

    public $tokenCreado;

    /**
     * Create a new notification instance.
     */
    public function __construct($tokenCreado)
    {
        $this->tokenCreado = $tokenCreado;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route("verificarcuenta", $this->tokenCreado);
        return (new MailMessage)
                    ->line('Primero de todo, ¡bienvenido a la cola!')
                    ->line('Este mensaje se ha generado de forma automática para que verifiques tu cuenta de usuario y poder así disfrutar de todas las funcionalidades.')
                    ->line('Para hacerlo, solo tienes que pulsar en el enlace que encontrarás a continuación:')
                    ->action('Verificar cuenta', $url)
                    ->line('Un saludo desde ' . env("APP_NAME"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
