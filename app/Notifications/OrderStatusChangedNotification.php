<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected string $newStatus,
        protected ?string $newPaymentStatus = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $statusMessages = [
            'pending' => 'menunggu konfirmasi',
            'processing' => 'sedang diproses oleh seller',
            'shipped' => 'sudah dikirim',
            'delivered' => 'sudah sampai',
            'completed' => 'sudah selesai',
            'cancelled' => 'dibatalkan',
        ];

        $paymentMessages = [
            'pending' => 'menunggu pembayaran',
            'paid' => 'pembayaran diterima',
            'failed' => 'pembayaran gagal',
            'refunded' => 'dana dikembalikan',
        ];

        $title = 'Status Pesanan Diperbarui';
        $message = "Pesanan #{$this->order->order_number} ";
        $icon = 'package';

        // Determine message based on status change
        if ($this->newPaymentStatus === 'paid') {
            $title = 'Pembayaran Diterima';
            $message .= 'pembayaran telah dikonfirmasi. Pesanan akan segera diproses.';
            $icon = 'credit-card';
        } elseif ($this->newStatus === 'processing') {
            $title = 'Pesanan Diproses';
            $message .= 'sedang diproses oleh seller.';
            $icon = 'package';
        } elseif ($this->newStatus === 'shipped') {
            $title = 'Pesanan Dikirim';
            $message .= 'sedang dalam perjalanan ke tujuan.';
            $icon = 'truck';
        } elseif ($this->newStatus === 'delivered') {
            $title = 'Pesanan Sampai';
            $message .= 'sudah sampai di tujuan.';
            $icon = 'check-circle';
        } elseif ($this->newStatus === 'completed') {
            $title = 'Pesanan Selesai';
            $message .= 'sudah selesai. Terima kasih telah berbelanja!';
            $icon = 'check-circle';
        } elseif ($this->newStatus === 'cancelled') {
            $title = 'Pesanan Dibatalkan';
            $message .= 'telah dibatalkan.';
            $icon = 'x-circle';
        } else {
            $message .= $statusMessages[$this->newStatus] ?? 'status diperbarui.';
        }

        return [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'action_url' => '/customer/dashboard/transactions',
            'order_id' => $this->order->id,
            'status' => $this->newStatus,
            'payment_status' => $this->newPaymentStatus,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $data = $this->toDatabase($notifiable);

        return new BroadcastMessage([
            'notification' => [
                'id' => null,
                'title' => $data['title'],
                'message' => $data['message'],
                'icon' => $data['icon'],
                'action_url' => $data['action_url'],
                'order_id' => $data['order_id'],
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
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $this->order->user_id . '.notifications'),
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
        $this->order->loadMissing(['items', 'store']);

        $orderNumber = $this->order->order_number;
        $storeName = $this->order->store?->name ?? 'Toko';
        $grandTotal = number_format($this->order->grand_total, 0, ',', '.');
        $trackingNumber = $this->order->shipping_awb ?? null;

        // Build items list
        $itemsList = $this->order->items->map(function ($item) {
            return "• {$item->name} (x{$item->quantity})";
        })->join("\n");

        $mail = new MailMessage;

        // Dynamic content based on status
        if ($this->newPaymentStatus === 'paid') {
            $mail->subject("✅ Pembayaran Dikonfirmasi - Pesanan #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Kabar baik! Pembayaran untuk pesanan Anda telah dikonfirmasi.")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}")
                ->line("🏪 **Toko:** {$storeName}")
                ->line("💰 **Total:** Rp {$grandTotal}")
                ->line("---")
                ->line("Pesanan Anda akan segera diproses oleh seller. Kami akan mengirimkan notifikasi saat pesanan dikirim.")
                ->action('Lihat Status Pesanan', url('/customer/dashboard/transactions'));

        } elseif ($this->newStatus === 'processing') {
            $mail->subject("📦 Pesanan Sedang Diproses - #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Pesanan Anda sedang diproses oleh **{$storeName}**.")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}")
                ->line("**Produk:**")
                ->line($itemsList)
                ->line("---")
                ->line("Seller sedang menyiapkan pesanan Anda. Kami akan mengirimkan notifikasi saat pesanan sudah dikirim.")
                ->action('Lihat Status Pesanan', url('/customer/dashboard/transactions'));

        } elseif ($this->newStatus === 'shipped') {
            $mail->subject("🚚 Pesanan Dalam Pengiriman - #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Pesanan Anda dari **{$storeName}** sudah dikirim!")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}");

            if ($trackingNumber) {
                $mail->line("📋 **No. Resi:** {$trackingNumber}");
            }

            $mail->line("**Produk:**")
                ->line($itemsList)
                ->line("---")
                ->line("Pesanan Anda sedang dalam perjalanan. Pastikan alamat dan nomor telepon Anda dapat dihubungi.")
                ->action('Lacak Pesanan', url('/customer/dashboard/transactions'));

        } elseif ($this->newStatus === 'delivered') {
            $mail->subject("📬 Pesanan Sudah Sampai - #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Pesanan Anda dari **{$storeName}** sudah sampai di tujuan!")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}")
                ->line("**Produk:**")
                ->line($itemsList)
                ->line("---")
                ->line("Mohon konfirmasi penerimaan pesanan di aplikasi kami.")
                ->action('Konfirmasi Pesanan', url('/customer/dashboard/transactions'));

        } elseif ($this->newStatus === 'completed') {
            $mail->subject("🎉 Pesanan Selesai - #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Terima kasih! Pesanan Anda dari **{$storeName}** telah selesai.")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}")
                ->line("**Produk:**")
                ->line($itemsList)
                ->line("---")
                ->line("Bagaimana pengalaman belanja Anda? Berikan ulasan untuk membantu pembeli lainnya!")
                ->action('Beri Ulasan', url('/customer/dashboard/transactions'))
                ->line('Terima kasih telah berbelanja. Sampai jumpa di pesanan berikutnya!');

        } elseif ($this->newStatus === 'cancelled') {
            $mail->subject("❌ Pesanan Dibatalkan - #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Mohon maaf, pesanan Anda dari **{$storeName}** telah dibatalkan.")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}")
                ->line("💰 **Total:** Rp {$grandTotal}")
                ->line("**Produk:**")
                ->line($itemsList)
                ->line("---")
                ->line("Jika Anda memiliki pertanyaan, silakan hubungi customer service kami.")
                ->action('Hubungi Kami', url('/customer/dashboard/transactions'));

        } else {
            $mail->subject("📋 Status Pesanan Diperbarui - #{$orderNumber}")
                ->greeting("Halo, {$notifiable->name}!")
                ->line("Status pesanan Anda telah diperbarui.")
                ->line("---")
                ->line("📦 **No. Pesanan:** {$orderNumber}")
                ->line("🏪 **Toko:** {$storeName}")
                ->line("---")
                ->action('Lihat Detail', url('/customer/dashboard/transactions'));
        }

        return $mail;
    }
}

