<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetearContrasena extends Notification
{
    use Queueable;

    private $tokenCreado;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->tokenCreado = $token;
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
        $url = route("resetearContrasena", $this->tokenCreado);

        return (new MailMessage)
                    ->line('Hemos recibido una solicitud para resetear tu contraseña')
                    ->line("Con el siguiente enlace podrás cambiarla:")
                    ->action('Notification Action', $url)
                    ->line('Espero te sirva. ¡Un saludo desde '.env("APP_NAME").'!');
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
