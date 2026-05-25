<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Новый заказ #'.$this->order->id)
            ->line('Поступил новый заказ от '.$this->order->user->name.'.')
            ->line('Сумма: '.$this->order->total)
            ->action('Просмотреть заказ', url('/orders/'.$this->order->id));
    }
}
