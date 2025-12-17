<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus,
        public ?string $previousPaymentStatus = null,
        public ?string $newPaymentStatus = null,
    ) {
    }

    public function build(): self
    {
        $storeName = $this->order->store?->name ?? 'Toko';
        $orderNumber = $this->order->order_number;

        return $this->subject("Update Pesanan #{$orderNumber} - {$storeName}")
            ->view('emails.order-status-changed')
            ->with([
                'order' => $this->order,
                'previousStatus' => $this->previousStatus,
                'newStatus' => $this->newStatus,
                'previousPaymentStatus' => $this->previousPaymentStatus,
                'newPaymentStatus' => $this->newPaymentStatus,
                'statusLabel' => $this->getStatusLabel($this->newStatus),
                'paymentStatusLabel' => $this->newPaymentStatus
                    ? $this->getPaymentStatusLabel($this->newPaymentStatus)
                    : null,
                'storeName' => $storeName,
            ]);
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending_payment' => 'Menunggu Pembayaran',
            'processing' => 'Sedang Diproses',
            'shipped' => 'Dalam Pengiriman',
            'delivered' => 'Sudah Diterima',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    private function getPaymentStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'expired' => 'Kedaluwarsa',
            'failed' => 'Gagal',
            default => $status,
        };
    }
}
