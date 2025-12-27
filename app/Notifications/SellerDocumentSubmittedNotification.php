<?php

namespace App\Notifications;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerDocumentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Store $store
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sellerDocumentId = $this->store->sellerDocument?->id;

        return [
            'title' => 'Dokumen Seller Baru',
            'message' => "Toko \"{$this->store->name}\" mengajukan verifikasi dokumen.",
            'icon' => 'file-check',
            'action_url' => $sellerDocumentId ? "/admin/seller-documents/{$sellerDocumentId}" : "/admin/seller-documents",
            'store_id' => $this->store->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sellerDocumentId = $this->store->sellerDocument?->id;
        $ownerName = $this->store->user?->name ?? 'Seller';
        $ownerEmail = $this->store->user?->email ?? '-';
        $actionUrl = url($sellerDocumentId ? "/admin/seller-documents/{$sellerDocumentId}" : "/admin/seller-documents");

        return (new MailMessage)
            ->subject('Dokumen Seller Baru Perlu Diverifikasi')
            ->view('emails.seller-document-submitted', [
                'store' => $this->store,
                'ownerName' => $ownerName,
                'ownerEmail' => $ownerEmail,
                'actionUrl' => $actionUrl,
            ]);
    }
}
