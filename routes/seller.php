<?php

use App\Http\Controllers\Seller\ChatController;
use App\Http\Controllers\Seller\CustomerController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\NotificationController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\ProfileController;

use App\Http\Controllers\Seller\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('seller.dashboard.index'))->name('dashboard');

Route::get('dashboard', DashboardController::class)->name('dashboard.index');

// Redirect old documents route to settings
Route::get('documents', fn () => redirect()->route('seller.settings.edit'))->name('documents.show');

// Settings with document management
Route::get('settings', [StoreController::class, 'edit'])->name('settings.edit');
Route::post('settings', [StoreController::class, 'store'])->name('settings.store');
Route::put('settings', [StoreController::class, 'update'])->name('settings.update');
Route::post('settings/submit', [StoreController::class, 'submit'])->name('settings.submit');
Route::delete('settings/documents/supporting/{mediaId}', [StoreController::class, 'deleteSupportingDocument'])->name('settings.documents.supporting.destroy');

Route::resource('products', ProductController::class)->except(['show']);

Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');

// Chat routes
Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
Route::get('chats/unread-count', [ChatController::class, 'unreadCount'])->name('chats.unread-count');
Route::get('chats/{conversation}', [ChatController::class, 'show'])->name('chats.show');
Route::post('chats/{conversation}/messages', [ChatController::class, 'storeMessage'])->name('chats.messages.store');
Route::get('chats/{conversation}/messages', [ChatController::class, 'getMessages'])->name('chats.messages.index');



// Profile routes
Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// Notification routes
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::delete('notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
