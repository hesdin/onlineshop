<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreRejectedNotification extends Notification implements ShouldQueue
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
        $documentsUrl = url('/seller/documents');

        return (new MailMessage)
            ->subject('Verifikasi Toko Ditolak - Tindakan Diperlukan')
            ->view('emails.store-rejected', [
                'user' => $notifiable,
                'storeName' => $storeName,
                'reason' => $this->reason,
                'documentsUrl' => $documentsUrl,
            ]);
    }
}
