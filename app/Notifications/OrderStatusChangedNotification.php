<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected string $newStatus,
        protected ?string $newPaymentStatus = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
