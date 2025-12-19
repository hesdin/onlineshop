<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
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
            'title' => 'Pesanan Baru',
            'message' => "Pesanan #{$this->order->order_number} senilai Rp " . number_format($this->order->grand_total, 0, ',', '.'),
            'icon' => 'shopping-bag',
            'action_url' => "/seller/orders/{$this->order->id}",
            'order_id' => $this->order->id,
        ];
    }
}
