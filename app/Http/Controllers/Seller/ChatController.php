<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $conversations = Conversation::with(['customer:id,name', 'product:id,name'])
            ->withCount(['unreadMessagesForSeller as unread_count'])
            ->where('store_id', $store->id)
            ->orderByDesc('last_message_at')
            ->paginate(20);

        // Get latest message for each conversation
        $conversations->getCollection()->transform(function ($conversation) {
            $conversation->last_message = $conversation->messages()->latest()->first();
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

        $message->load('sender:id,name');

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
}
