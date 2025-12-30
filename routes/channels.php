<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Authorization for conversation private channel.
 * Allows access if user is the customer or the seller (store owner) of the conversation.
 */
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // Customer can access their own conversations
    if ($conversation->customer_id === $user->id) {
        return true;
    }

    // Seller can access conversations with their store
    if ($user->store && $user->store->id === $conversation->store_id) {
        return true;
    }

    return false;
});

/**
 * Authorization for store conversations channel.
 * Allows seller to receive updates for their store's conversation list.
 */
Broadcast::channel('store.{storeId}.conversations', function ($user, $storeId) {
    // Only the store owner can access this channel
    return $user->store && $user->store->id === (int) $storeId;
});

/**
 * Authorization for user notifications channel.
 * Allows user to receive their own notifications in real-time.
 */
Broadcast::channel('user.{userId}.notifications', function ($user, $userId) {
    return $user->id === (int) $userId;
});
