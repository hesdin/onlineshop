<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $productName,
        protected int $currentStock,
        protected int $productId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Stok Menipis',
            'message' => "Produk \"{$this->productName}\" tersisa {$this->currentStock} unit.",
            'icon' => 'alert-triangle',
            'action_url' => "/seller/products/{$this->productId}/edit",
            'product_id' => $this->productId,
        ];
    }
}
