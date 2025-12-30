<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcasted when a customer receives a new message.
 * Used to update the chat badge and list in LandingHeader real-time.
 */
class CustomerConversationUpdated implements ShouldBroadcast
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
        $this->conversation->load(['store:id,name', 'product:id,name']);
        $this->message->load('sender:id,name');
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->conversation->customer_id . '.notifications'),
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
                'store' => $this->conversation->store ? [
                    'id' => $this->conversation->store->id,
                    'name' => $this->conversation->store->name,
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
