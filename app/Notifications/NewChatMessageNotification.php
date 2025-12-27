<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Conversation;
use App\Models\Store;

class NewChatMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Store $store,
        public string $messagePreview,
        public Conversation $conversation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $chatUrl = url("/customer/chat/{$this->conversation->id}");

        return (new MailMessage)
            ->subject("Pesan baru dari {$this->store->name}")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Anda menerima pesan baru dari **{$this->store->name}**:")
            ->line("\"{$this->messagePreview}\"")
            ->action('Balas Pesan', $chatUrl)
            ->line('Terima kasih telah berbelanja di toko kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'conversation_id' => $this->conversation->id,
            'message_preview' => $this->messagePreview,
        ];
    }
}
