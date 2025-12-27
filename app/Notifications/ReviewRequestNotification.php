<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewRequestNotification extends Notification
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
            'title' => 'Bagaimana Pesanan Anda?',
            'message' => "Pesanan #{$this->order->order_number} sudah selesai. Yuk beri ulasan untuk produk yang Anda beli!",
            'icon' => 'star',
            'action_url' => '/customer/dashboard/reviews',
            'order_id' => $this->order->id,
        ];
    }
}
