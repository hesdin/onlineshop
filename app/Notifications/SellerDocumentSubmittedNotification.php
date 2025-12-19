<?php

namespace App\Notifications;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerDocumentSubmittedNotification extends Notification
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

        return (new MailMessage)
            ->subject('Dokumen Seller Baru Perlu Diverifikasi')
            ->greeting('Halo Admin!')
            ->line("Ada pengajuan verifikasi dokumen baru yang perlu ditinjau:")
            ->line("**Nama Toko:** {$this->store->name}")
            ->line("**Jenis Toko:** " . ucfirst($this->store->type ?? '-'))
            ->line("**Pemilik:** {$ownerName} ({$ownerEmail})")
            ->action('Review Dokumen', url($sellerDocumentId ? "/admin/seller-documents/{$sellerDocumentId}" : "/admin/seller-documents"))
            ->line('Silakan verifikasi dokumen seller dalam 1-3 hari kerja.');
    }
}
