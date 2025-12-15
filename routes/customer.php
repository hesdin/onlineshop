<?php

use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\ProfileController;
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

    Route::get('address', [AddressController::class, 'index'])->name('address');
    Route::post('address', [AddressController::class, 'store'])->name('address.store');
    Route::put('address/{address}', [AddressController::class, 'update'])->name('address.update');
    Route::post('address/{address}/select', [AddressController::class, 'select'])->name('address.select');
    Route::delete('address/{address}', [AddressController::class, 'destroy'])->name('address.destroy');

    Route::get('payment', function () {
        return Inertia::render('Customer/Payment');
    })->name('payment');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
});
