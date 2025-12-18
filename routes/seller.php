<?php

use App\Http\Controllers\Seller\CustomerController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\SellerDocumentController;
use App\Http\Controllers\Seller\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('seller.dashboard.index'))->name('dashboard');

Route::get('dashboard', DashboardController::class)->name('dashboard.index');

// Document upload for seller verification
Route::get('documents', [SellerDocumentController::class, 'show'])->name('documents.show');
Route::post('documents', [SellerDocumentController::class, 'update'])->name('documents.update');
Route::post('documents/submit', [SellerDocumentController::class, 'submit'])->name('documents.submit');
Route::delete('documents/supporting/{mediaId}', [SellerDocumentController::class, 'deleteSupportingDocument'])->name('documents.supporting.destroy');

Route::resource('products', ProductController::class)->except(['show']);

Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');

Route::get('store', [StoreController::class, 'edit'])->name('store.edit');
Route::post('store', [StoreController::class, 'store'])->name('store.store');
Route::put('store', [StoreController::class, 'update'])->name('store.update');

