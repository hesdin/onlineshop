<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ?string $storeName = null,
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Verifikasi Toko Ditolak',
            'message' => 'Dokumen toko Anda tidak dapat diverifikasi. Silakan periksa catatan admin.',
            'icon' => 'x-circle',
            'action_url' => '/seller/documents',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $storeName = $this->storeName ?? 'Toko Anda';
        $mail = (new MailMessage)
            ->subject('Verifikasi Toko Ditolak - Tindakan Diperlukan')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line("Mohon maaf, pengajuan verifikasi toko **{$storeName}** belum dapat kami setujui.");

        if ($this->reason) {
            $mail->line('**Catatan dari tim verifikasi:**')
                ->line($this->reason);
        }

        return $mail
            ->line('Silakan periksa kembali dokumen Anda dan ajukan ulang verifikasi.')
            ->action('Periksa Dokumen', url('/seller/documents'))
            ->line('Jika Anda memiliki pertanyaan, silakan hubungi tim support kami.');
    }
}
