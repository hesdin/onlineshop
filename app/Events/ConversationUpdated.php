<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcasted when a new message is received in a store's conversation.
 * Used to update the conversation list in real-time.
 */
class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Conversation $conversation,
        public Message $message
    ) {
        // Load necessary relationships
        $this->conversation->load(['customer:id,name', 'product:id,name']);
        $this->message->load('sender:id,name');
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->conversation->store_id . '.conversations'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'id' => $this->conversation->id,
                'customer' => $this->conversation->customer ? [
                    'id' => $this->conversation->customer->id,
                    'name' => $this->conversation->customer->name,
                ] : null,
                'product' => $this->conversation->product ? [
                    'id' => $this->conversation->product->id,
                    'name' => $this->conversation->product->name,
                ] : null,
                'last_message_at' => $this->conversation->last_message_at?->toISOString(),
            ],
            'last_message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'sender_type' => $this->message->sender_type,
                'created_at' => $this->message->created_at->toISOString(),
            ],
        ];
    }
}
