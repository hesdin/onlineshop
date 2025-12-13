<?php

use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('seller.dashboard.index'))->name('dashboard');

Route::get('dashboard', DashboardController::class)->name('dashboard.index');

Route::resource('products', ProductController::class)->except(['show']);

Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);

Route::get('store', [StoreController::class, 'edit'])->name('store.edit');
Route::post('store', [StoreController::class, 'store'])->name('store.store');
Route::put('store', [StoreController::class, 'update'])->name('store.update');
