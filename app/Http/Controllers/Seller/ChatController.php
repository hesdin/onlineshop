<?php

namespace App\Http\Controllers\Seller;

use App\Events\CustomerConversationUpdated;
use App\Events\MessageSent;
use App\Events\NotificationReceived;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewChatMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    /**
     * Display list of conversations for the seller's store.
     */
    public function index(Request $request): Response
    {
        $store = $request->user()->store;

        if (!$store) {
            return Inertia::render('Seller/Chat/Index', [
                'conversations' => [],
            ]);
        }

        $conversations = Conversation::with(['customer:id,name,last_active_at', 'product:id,name'])
            ->withCount(['unreadMessagesForSeller as unread_count'])
            ->where('store_id', $store->id)
            ->orderByDesc('last_message_at')
            ->paginate(20);

        // Get latest message for each conversation and add online status
        $conversations->getCollection()->transform(function ($conversation) {
            // Use reorder() to clear default ASC ordering from relationship
            $conversation->last_message = $conversation->messages()->reorder()->orderByDesc('id')->first();
            $conversation->customer_is_online = $conversation->customer?->isOnline() ?? false;
            return $conversation;
        });

        return Inertia::render('Seller/Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Display a specific conversation with messages.
     */
    public function show(Request $request, Conversation $conversation): Response
    {
        $store = $request->user()->store;

        // Ensure seller owns this conversation
        if (!$store || $conversation->store_id !== $store->id) {
            abort(403, 'Unauthorized');
        }

        // Mark all customer messages as read
        $conversation->unreadMessagesForSeller()->update(['read_at' => now()]);

        $conversation->load([
            'customer:id,name',
            'product:id,name,slug',
            'messages.sender:id,name',
        ]);

        return Inertia::render('Seller/Chat/Show', [
            'conversation' => $conversation,
        ]);
    }

    /**
     * Store a new message from seller.
     */
    public function storeMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $store = $request->user()->store;

        // Ensure seller owns this conversation
        if (!$store || $conversation->store_id !== $store->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'sender_type' => 'seller',
            'content' => $validated['content'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Check if customer is offline and send email notification
        $customer = $conversation->customer;
        if ($customer && !$customer->isOnline()) {
            $messagePreview = \Illuminate\Support\Str::limit($validated['content'], 100);
            Notification::sendNow($customer, new NewChatMessageNotification(
                $store,
                $messagePreview,
                $conversation
            ));

            // Dispatch realtime event for customer
            $latestNotification = $customer->notifications()->latest()->first();
            if ($latestNotification) {
                event(new NotificationReceived($customer, [
                    'id' => $latestNotification->id,
                    'type' => class_basename($latestNotification->type),
                    'title' => $latestNotification->data['title'] ?? 'Notifikasi',
                    'message' => $latestNotification->data['message'] ?? '',
                    'icon' => $latestNotification->data['icon'] ?? 'bell',
                    'action_url' => $latestNotification->data['action_url'] ?? null,
                    'read_at' => null,
                    'created_at' => $latestNotification->created_at->diffForHumans(),
                ]));
            }
        }

        $message->load('sender:id,name');

        // Broadcast message to other participants
        broadcast(new MessageSent($message, $conversation))->toOthers();

        // Refresh conversation to get updated last_message_at
        $conversation->refresh();

        // Notify customer's chat list (for LandingHeader badge)
        broadcast(new CustomerConversationUpdated($conversation, $message));

        return response()->json([
            'message' => $message,
        ]);
    }

    /**
     * Get new messages for polling (AJAX).
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store || $conversation->store_id !== $store->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = $request->query('after_id', 0);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->where('id', '>', $afterId)
            ->get();

        // Mark customer messages as read
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->where('id', '>', $afterId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Get unread count for notification badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['unread_count' => 0]);
        }

        $count = Message::whereHas('conversation', function ($q) use ($store) {
            $q->where('store_id', $store->id);
        })
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Mark all messages in a conversation as read by seller.
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $store = $request->user()->store;

        // Ensure seller owns this conversation
        if (!$store || $conversation->store_id !== $store->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark all unread customer messages as read
        $conversation->unreadMessagesForSeller()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
