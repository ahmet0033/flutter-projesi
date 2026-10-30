<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Sipariş durumu güncellendi',
            'body' => "{$this->order->number} numaralı siparişiniz: {$this->status}.",
            'order_number' => $this->order->number,
            'status' => $this->status,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('RedEast sipariş durumu: '.$this->order->number)
            ->greeting('Merhaba '.$notifiable->name)
            ->line('Sipariş durumunuz güncellendi: '.$this->status)
            ->line('Sipariş numarası: '.$this->order->number);
    }
}
