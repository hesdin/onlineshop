<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Home & Content
Route::get('home', [HomeController::class, 'index']);
Route::get('banners', [BannerController::class, 'index']);
Route::get('collections/{slug}', [CollectionController::class, 'show']);

// Categories
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{slug}/products', [CategoryController::class, 'products']);

// Products
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/reviews', [ProductController::class, 'reviews']);

// Stores
Route::get('stores/{slug}', [StoreController::class, 'show']);
Route::get('stores/{slug}/products', [StoreController::class, 'products']);

// Regions
Route::prefix('regions')->group(function () {
    Route::get('provinces', [RegionController::class, 'provinces']);
    Route::get('cities', [RegionController::class, 'cities']);
    Route::get('districts', [RegionController::class, 'districts']);
});

// ============================================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::put('user', [AuthController::class, 'updateProfile']);
    });

    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('items', [CartController::class, 'addItem']);
        Route::patch('items/{item}', [CartController::class, 'updateItem']);
        Route::delete('items/{item}', [CartController::class, 'removeItem']);
    });

    // Addresses
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::put('{address}', [AddressController::class, 'update']);
        Route::delete('{address}', [AddressController::class, 'destroy']);
        Route::post('{address}/default', [AddressController::class, 'setDefault']);
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('{order}', [OrderController::class, 'show']);
    });

    // Reviews
    Route::get('reviews', [ReviewController::class, 'index']);
    Route::post('reviews', [ReviewController::class, 'store']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
    });
});
