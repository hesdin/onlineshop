<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $productName,
        protected int $currentStock,
        protected int $productId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Stok Menipis',
            'message' => "Stok \"{$this->productName}\" sisa {$this->currentStock} unit.",
            'icon' => 'alert-triangle',
            'action_url' => "/seller/products/{$this->productId}/edit",
            'product_id' => $this->productId,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stockWarning = $this->currentStock <= 5 ? '⚠️ SANGAT RENDAH' : '⚠️ Menipis';

        return (new MailMessage)
            ->subject("⚠️ Stok Menipis - {$this->productName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Stok produk Anda hampir habis dan perlu segera diisi ulang.")
            ->line("---")
            ->line("**Detail Produk:**")
            ->line("📦 **Nama:** {$this->productName}")
            ->line("📊 **Sisa Stok:** {$this->currentStock} unit {$stockWarning}")
            ->line("---")
            ->line("Segera update stok produk Anda untuk menghindari kehabisan stok dan kehilangan penjualan.")
            ->action('Update Stok Sekarang', url("/seller/products/{$this->productId}/edit"))
            ->line('Terima kasih telah menggunakan platform kami!');
    }
}

