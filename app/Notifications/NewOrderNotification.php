<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle commande #{$this->order->id}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vous avez reçu une nouvelle commande (#**{$this->order->id}**) d'un montant de **{$this->order->total} Ar**.")
            ->action('Voir la commande', url('/vendor/orders'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'new_order',
            'order_id'   => $this->order->id,
            'total'      => $this->order->total,
            'message'    => "Nouvelle commande #{$this->order->id} — {$this->order->total} Ar",
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
