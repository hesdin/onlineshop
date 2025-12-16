<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CustomerSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SellerSessionController;
use App\Http\Controllers\Auth\VerifyAndSetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartPageController;
use App\Http\Controllers\CategoryIndexPageController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\CheckoutPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\ProductPageController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SearchPageController;
use App\Http\Controllers\StorePageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');
Route::get('/c', CategoryIndexPageController::class)->name('category.index');
Route::get('/c/{category:slug}', CategoryPageController::class)->name('category.show');
Route::get('/product/{slug}/{product}', ProductPageController::class)->name('product.detail');
Route::get('/store/{store:slug}', StorePageController::class)->name('store.show');
Route::get('/search', SearchPageController::class)->name('search');
Route::middleware(['auth', 'role:customer'])
    ->prefix('cart')
    ->name('cart.')
    ->group(function () {
        Route::get('/', CartPageController::class)->name('index');
        Route::post('/', [CartController::class, 'store'])->name('store');
        Route::patch('/items/{item}', [CartController::class, 'update'])->name('items.update');
        Route::put('/items/{item}/note', [CartController::class, 'updateNote'])->name('items.note.update');
        Route::put('/items/{item}/shipping', [CartController::class, 'updateShippingMethod'])->name('items.shipping.update');
        Route::delete('/items/{item}', [CartController::class, 'destroy'])->name('items.destroy');
        Route::get('/checkout', CheckoutPageController::class)->name('checkout');
        Route::get('/payment', PaymentPageController::class)->name('payment');
        Route::post('/payment/process', [App\Http\Controllers\OrderController::class, 'createOrder'])->name('payment.process');
    });

Route::middleware(['auth', 'role:customer'])
    ->prefix('orders')
    ->name('orders.')
    ->group(function () {
        Route::get('/confirmation', [App\Http\Controllers\OrderController::class, 'confirmation'])->name('confirmation');
        Route::get('/{order}/invoice', [App\Http\Controllers\InvoiceController::class, 'download'])->name('invoice.download');
    });

Route::get('/login', function () {
    return Inertia::render('Auth/LoginAs');
})->name('portal.login');

Route::get('/register-as', function () {
    return Inertia::render('Auth/RegisterAs');
})->name('portal.register-as');

Route::get('/register', function (Request $request) {
    $role = $request->query('role');

    if ($role === 'seller') {
        return Inertia::render('Auth/RegisterSeller');
    }

    return Inertia::render('Auth/RegisterCustomer');
})->name('register');

Route::post('/register/customer', [RegisterController::class, 'storeCustomer'])->name('register.customer');
Route::post('/register/seller', [RegisterController::class, 'storeSeller'])->name('register.seller');

Route::get('/verify-and-set-password/{id}/{hash}', [VerifyAndSetPasswordController::class, 'show'])
    ->middleware('signed')
    ->name('verification.set-password');

Route::post('/verify-and-set-password/{id}/{hash}', [VerifyAndSetPasswordController::class, 'update'])
    ->middleware('signed')
    ->name('verification.set-password.update');

Route::inertia('/forgot-password', 'Auth/ForgotPassword')->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
Route::get('/reset-password/{id}/{hash}', [PasswordResetController::class, 'show'])
    ->middleware('signed')
    ->name('password.reset');
Route::post('/reset-password/{id}/{hash}', [PasswordResetController::class, 'update'])
    ->middleware('signed')
    ->name('password.reset.update');

Route::prefix('regions')->name('regions.')->group(function () {
    Route::get('provinces', [RegionController::class, 'provinces'])->name('provinces');
    Route::get('cities', [RegionController::class, 'cities'])->name('cities');
    Route::get('districts', [RegionController::class, 'districts'])->name('districts');
});

Route::middleware('guest')->prefix('admin')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/force-password', [VerifyAndSetPasswordController::class, 'forceForm'])->name('password.force');
    Route::post('/force-password', [VerifyAndSetPasswordController::class, 'forceUpdate'])->name('password.force.update');
});

Route::prefix('seller')->name('seller.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [SellerSessionController::class, 'create'])->name('login');
        Route::post('login', [SellerSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'role:seller'])->group(function () {
        require base_path('routes/seller.php');
    });
});

Route::prefix('customer')->name('customer.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [CustomerSessionController::class, 'create'])->name('login');
        Route::post('login', [CustomerSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'role:customer'])->group(function () {
        require base_path('routes/customer.php');
    });
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require base_path('routes/admin.php');
    });

Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        require base_path('routes/customer.php');
    });

Route::prefix('template')->group(function () {
    require base_path('routes/template.php');
});
