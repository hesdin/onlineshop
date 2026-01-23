<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreVerifiedNotification extends Notification implements ShouldQueue
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
            'message' => 'Selamat! Toko Anda telah diverifikasi dan siap berjualan.',
            'icon' => 'check-circle',
            'action_url' => '/seller/dashboard',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $storeName = $this->storeName ?? 'Toko Anda';
        $dashboardUrl = url('/seller/dashboard');

        return (new MailMessage)
            ->subject('Selamat! Toko Anda Telah Terverifikasi')
            ->view('emails.store-verified', [
                'user' => $notifiable,
                'storeName' => $storeName,
                'dashboardUrl' => $dashboardUrl,
            ]);
    }
}
