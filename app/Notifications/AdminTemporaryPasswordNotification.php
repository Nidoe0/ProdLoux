<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminTemporaryPasswordNotification extends Notification
{
    use Queueable;

    public $temporaryPassword;

    public function __construct(string $temporaryPassword)
    {
        $this->temporaryPassword = $temporaryPassword;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre mot de passe temporaire administrateur')
            ->greeting('Bonjour ' . ($notifiable->name ?? ''))
            ->line('Vous avez demandé la récupération de votre compte administrateur.')
            ->line('Voici un mot de passe temporaire que vous pouvez utiliser pour vous connecter :')
            ->line('Mot de passe temporaire : ' . $this->temporaryPassword)
            ->line('Pour des raisons de sécurité, changez ce mot de passe dès votre connexion.')
            ->line('Si vous n\'avez pas demandé ce mot de passe, contactez immédiatement l\'équipe d\'administration.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'temporaryPassword' => $this->temporaryPassword,
        ];
    }
}
