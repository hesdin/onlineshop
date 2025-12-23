<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['items', 'user', 'paymentMethod']);

        $storeName = $this->order->store?->name ?? 'Toko Anda';
        $orderNumber = $this->order->order_number;
        $customerName = $this->order->user?->name ?? 'Pelanggan';
        $grandTotal = number_format($this->order->grand_total, 0, ',', '.');
        $paymentMethod = $this->order->paymentMethod?->name ?? 'N/A';
        $orderedAt = $this->order->ordered_at?->format('d M Y H:i') ?? now()->format('d M Y H:i');

        // Build items list
        $itemsList = $this->order->items->map(function ($item) {
            $subtotal = number_format($item->subtotal, 0, ',', '.');
            return "• {$item->name} (x{$item->quantity}) - Rp {$subtotal}";
        })->join("\n");

        return (new MailMessage)
            ->subject("🛒 Pesanan Baru #{$orderNumber} - {$storeName}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Selamat! Anda mendapatkan pesanan baru di toko **{$storeName}**.")
            ->line("---")
            ->line("**Detail Pesanan:**")
            ->line("📦 **No. Pesanan:** {$orderNumber}")
            ->line("👤 **Pelanggan:** {$customerName}")
            ->line("📅 **Waktu Pesan:** {$orderedAt}")
            ->line("💳 **Metode Pembayaran:** {$paymentMethod}")
            ->line("---")
            ->line("**Produk yang Dipesan:**")
            ->line($itemsList)
            ->line("---")
            ->line("💰 **Total Pembayaran:** Rp {$grandTotal}")
            ->action('Lihat Detail Pesanan', url("/seller/orders/{$this->order->id}"))
            ->line('Silakan segera proses pesanan ini. Terima kasih!');
    }
}
