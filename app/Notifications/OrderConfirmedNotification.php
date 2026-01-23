<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification implements ShouldBroadcastNow, ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    /**
     * Get contextual notification message based on payment and shipping method.
     */
    protected function getNotificationMessage(): string
    {
        $this->order->loadMissing(['paymentMethod']);

        $orderNumber = $this->order->order_number;
        $paymentCode = $this->order->paymentMethod?->code ?? '';
        $shippingService = $this->order->shipping_service ?? '';

        // Check for "Ambil di Toko" (pickup at store)
        $isPickup = str_contains(strtolower($shippingService), 'ambil') ||
                    str_contains(strtolower($shippingService), 'pickup');

        // Build contextual message (shortened for notification dropdown)
        if ($paymentCode === 'manual_transfer' || $paymentCode === 'bank_transfer') {
            if ($isPickup) {
                return "Pesanan #{$orderNumber} dibuat. Transfer ke rekening toko.";
            }

            return "Pesanan #{$orderNumber} dibuat. Transfer ke rekening toko.";
        } elseif ($paymentCode === 'cod') {
            if ($isPickup) {
                return "Pesanan #{$orderNumber} dibuat. Bayar saat ambil di toko.";
            }

            return "Pesanan #{$orderNumber} dibuat. Bayar saat terima.";
        }

        // Default message
        if ($isPickup) {
            return "Pesanan #{$orderNumber} dibuat. Ambil di toko.";
        }

        return "Pesanan #{$orderNumber} dibuat. Silakan bayar.";
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pesanan Dikonfirmasi',
            'message' => $this->getNotificationMessage(),
            'icon' => 'shopping-bag',
            'action_url' => '/customer/dashboard/transactions',
            'order_id' => $this->order->id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification' => [
                'id' => null,
                'title' => 'Pesanan Dikonfirmasi',
                'message' => $this->getNotificationMessage(),
                'icon' => 'shopping-bag',
                'action_url' => '/customer/dashboard/transactions',
                'order_id' => $this->order->id,
                'created_at' => now()->toISOString(),
                'read_at' => null,
            ],
        ]);
    }

    /**
     * Get the channels the notification should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.'.$this->order->user_id.'.notifications'),
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'NotificationReceived';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['items', 'store', 'paymentMethod']);

        $orderNumber = $this->order->order_number;
        $storeName = $this->order->store?->name ?? 'Toko';
        $grandTotal = number_format($this->order->grand_total, 0, ',', '.');
        $paymentMethod = $this->order->paymentMethod?->name ?? 'N/A';
        $orderedAt = $this->order->ordered_at?->format('d M Y H:i') ?? now()->format('d M Y H:i');

        // Build items list
        $itemsList = $this->order->items->map(function ($item) {
            $subtotal = number_format($item->subtotal, 0, ',', '.');

            return "• {$item->name} (x{$item->quantity}) - Rp {$subtotal}";
        })->join("\n");

        // Payment instructions based on method
        $paymentCode = $this->order->paymentMethod?->code ?? '';
        if ($paymentCode === 'cod') {
            $instructions = 'Pembayaran dilakukan langsung saat menerima pesanan (Cash on Delivery).';
        } else {
            $instructions = 'Silakan transfer ke rekening toko. Hubungi toko via WhatsApp untuk detail rekening bank.';
        }

        return (new MailMessage)
            ->subject("🛒 Pesanan #{$orderNumber} Berhasil Dibuat")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Terima kasih! Pesanan Anda di **{$storeName}** telah berhasil dibuat.")
            ->line('---')
            ->line('**Detail Pesanan:**')
            ->line("📦 **No. Pesanan:** {$orderNumber}")
            ->line("🏪 **Toko:** {$storeName}")
            ->line("📅 **Waktu Pesan:** {$orderedAt}")
            ->line("💳 **Metode Pembayaran:** {$paymentMethod}")
            ->line('---')
            ->line('**Produk yang Dipesan:**')
            ->line($itemsList)
            ->line('---')
            ->line("💰 **Total Pembayaran:** Rp {$grandTotal}")
            ->line('---')
            ->line('**Instruksi Pembayaran:**')
            ->line($instructions)
            ->action('Lihat Pesanan Saya', url('/customer/dashboard/transactions'))
            ->line('Terima kasih telah berbelanja!');
    }
}
