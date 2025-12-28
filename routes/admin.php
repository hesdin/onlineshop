<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SellerDocumentController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', DashboardController::class)->name('dashboard');
Route::get('search', [SearchController::class, 'search'])->name('search');
Route::resource('users', UserController::class)->except(['show']);
Route::resource('stores', StoreController::class)->except(['show']);
Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('products', ProductController::class)->except(['show']);
Route::resource('collections', CollectionController::class)->except(['show']);
Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
Route::resource('promo-codes', PromoCodeController::class)->except(['show']);
Route::resource('banners', BannerController::class)->except(['show']);

Route::get('seller-documents', [SellerDocumentController::class, 'index'])->name('seller-documents.index');
Route::get('seller-documents/{sellerDocument}', [SellerDocumentController::class, 'show'])->name('seller-documents.show');
Route::put('seller-documents/{sellerDocument}', [SellerDocumentController::class, 'update'])->name('seller-documents.update');

// Notification routes
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::delete('notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
