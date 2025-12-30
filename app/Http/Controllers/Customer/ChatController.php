<?php

namespace App\Http\Controllers\Customer;

use App\Events\ConversationUpdated;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    /**
     * Display chat list in dashboard (Inertia page).
     */
    public function dashboardIndex(Request $request): Response
    {
        $user = $request->user();

        $conversations = Conversation::with(['store:id,name', 'product:id,name'])
            ->withCount(['unreadMessagesForCustomer as unread_count'])
            ->where('customer_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) {
                $conv->last_message = $conv->messages()->latest()->first();
                $conv->store_logo = $conv->store?->getFirstMediaUrl('logo') ?: null;
                return $conv;
            });

        return Inertia::render('Customer/Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Display chat room in dashboard (Inertia page).
     */
    public function dashboardShow(Request $request, Conversation $conversation): Response
    {
        $user = $request->user();

        if ($conversation->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Mark seller messages as read
        $conversation->unreadMessagesForCustomer()->update(['read_at' => now()]);

        $conversation->load([
            'store:id,name',
            'product:id,name',
            'messages.sender:id,name',
        ]);

        $conversation->store_logo = $conversation->store?->getFirstMediaUrl('logo') ?: null;

        return Inertia::render('Customer/Chat/Show', [
            'conversation' => $conversation,
        ]);
    }

    /**
     * Start or continue a conversation with a store.
     */
    public function startChat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'customer_id' => $user->id,
                'store_id' => $validated['store_id'],
            ],
            [
                'product_id' => $validated['product_id'] ?? null,
            ]
        );

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'content' => $validated['message'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('sender:id,name');

        // Broadcast message to other participants
        broadcast(new MessageSent($message, $conversation))->toOthers();

        // Notify seller's conversation list
        broadcast(new ConversationUpdated($conversation, $message));

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => $message,
        ]);
    }

    /**
     * Get conversation with a store.
     */
    public function getConversation(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::with(['messages.sender:id,name', 'store:id,name'])
            ->where('customer_id', $user->id)
            ->where('store_id', $store->id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'conversation' => null,
                'messages' => [],
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'logo' => $store->logo_url,
                ],
            ]);
        }

        // Mark seller messages as read
        $conversation->unreadMessagesForCustomer()->update(['read_at' => now()]);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'logo' => $store->logo_url,
            ],
        ]);
    }

    /**
     * Send a message in existing conversation.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Ensure customer owns this conversation
        if ($conversation->customer_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'content' => $validated['content'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('sender:id,name');

        // Broadcast message to other participants
        broadcast(new MessageSent($message, $conversation))->toOthers();

        // Notify seller's conversation list
        broadcast(new ConversationUpdated($conversation, $message));

        return response()->json([
            'message' => $message,
        ]);
    }

    /**
     * Get new messages for polling.
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->customer_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = $request->query('after_id', 0);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->where('id', '>', $afterId)
            ->get();

        // Mark seller messages as read
        $conversation->messages()
            ->where('sender_type', 'seller')
            ->where('id', '>', $afterId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Get all conversations for customer.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::with(['store:id,name', 'product:id,name'])
            ->withCount(['unreadMessagesForCustomer as unread_count'])
            ->where('customer_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) {
                $conv->last_message = $conv->messages()->orderByDesc('created_at')->first();
                return $conv;
            });

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get unread count for notification.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = Message::whereHas('conversation', function ($q) use ($user) {
            $q->where('customer_id', $user->id);
        })
            ->where('sender_type', 'seller')
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Mark all messages in a conversation as read by customer.
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Ensure customer owns this conversation
        if ($conversation->customer_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark all unread seller messages as read
        $conversation->unreadMessagesForCustomer()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
