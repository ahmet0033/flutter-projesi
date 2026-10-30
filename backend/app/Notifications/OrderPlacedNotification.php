<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Siparişiniz alındı',
            'body' => "{$this->order->number} numaralı siparişiniz oluşturuldu.",
            'order_number' => $this->order->number,
            'url' => '/api/v1/orders/'.$this->order->number,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('RedEast siparişiniz alındı: '.$this->order->number)
            ->greeting('Merhaba '.$notifiable->name)
            ->line('Siparişiniz başarıyla oluşturuldu.')
            ->line('Sipariş numarası: '.$this->order->number)
            ->line('Toplam: $'.number_format($this->order->total_cents / 100, 2))
            ->action('Siparişi görüntüle', url('/api/v1/orders/'.$this->order->number));
    }
}
