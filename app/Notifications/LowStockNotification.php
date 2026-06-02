<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Stock bas — {$this->product->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le produit **{$this->product->name}** n'a plus que **{$this->product->stock}** unités en stock.")
            ->action('Gérer les produits', url('/vendor/products'))
            ->line('Pensez à réapprovisionner rapidement pour ne pas perdre de ventes.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'low_stock',
            'product_id'  => $this->product->id,
            'product_name'=> $this->product->name,
            'stock'       => $this->product->stock,
            'message'     => "Stock bas : {$this->product->name} ({$this->product->stock} restants)",
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
