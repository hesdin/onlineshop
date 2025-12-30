<?php

use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\ChatController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReorderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('customer.dashboard.profile');
    })->name('index');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('profile/logout-other-sessions', [ProfileController::class, 'logoutOtherSessions'])->name('profile.logout-other-sessions');

    Route::get('address', [AddressController::class, 'index'])->name('address');
    Route::post('address', [AddressController::class, 'store'])->name('address.store');
    Route::put('address/{address}', [AddressController::class, 'update'])->name('address.update');
    Route::post('address/{address}/select', [AddressController::class, 'select'])->name('address.select');
    Route::delete('address/{address}', [AddressController::class, 'destroy'])->name('address.destroy');


    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');

    Route::get('reorder', [ReorderController::class, 'index'])->name('reorder');

    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews');
    Route::post('reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Chat dashboard pages (Inertia)
    Route::get('chat', [ChatController::class, 'dashboardIndex'])->name('chat');
    Route::get('chat/{conversation}', [ChatController::class, 'dashboardShow'])->name('chat.show');
});

// Notification routes
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::delete('notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

// Chat API routes (JSON)
Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
Route::get('chats/unread-count', [ChatController::class, 'unreadCount'])->name('chats.unread-count');
Route::post('chats/start', [ChatController::class, 'startChat'])->name('chats.start');
Route::get('chats/store/{store}', [ChatController::class, 'getConversation'])->name('chats.store');
Route::post('chats/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chats.messages.store');
Route::get('chats/{conversation}/messages', [ChatController::class, 'getMessages'])->name('chats.messages.index');
Route::post('chats/{conversation}/read', [ChatController::class, 'markAsRead'])->name('chats.mark-read');
