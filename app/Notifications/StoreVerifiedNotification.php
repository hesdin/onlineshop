<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ?string $storeName = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Toko Terverifikasi',
            'message' => 'Selamat! Toko Anda telah berhasil diverifikasi dan siap menerima pesanan.',
            'icon' => 'check-circle',
            'action_url' => '/seller/dashboard',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $storeName = $this->storeName ?? 'Toko Anda';

        return (new MailMessage)
            ->subject('Selamat! Toko Anda Telah Terverifikasi')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line("Kabar baik! Toko **{$storeName}** telah berhasil diverifikasi oleh tim kami.")
            ->line('Toko Anda sekarang sudah dapat:')
            ->line('✅ Menerima pesanan dari customer')
            ->line('✅ Menampilkan produk di marketplace')
            ->line('✅ Memproses transaksi')
            ->action('Buka Dashboard', url('/seller/dashboard'))
            ->line('Terima kasih telah bergabung dengan marketplace kami!');
    }
}
