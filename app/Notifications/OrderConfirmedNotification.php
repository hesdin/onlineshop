<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pesanan Dikonfirmasi',
            'message' => "Pesanan #{$this->order->order_number} berhasil dibuat. Silakan lakukan pembayaran.",
            'icon' => 'shopping-bag',
            'action_url' => '/customer/dashboard/transactions',
            'order_id' => $this->order->id,
        ];
    }
}
