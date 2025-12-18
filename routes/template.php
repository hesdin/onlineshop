<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('category', function () {
    return view('category');
});

Route::get('collection/superdeal-road-to-pa-di-business-forum-and-showcase', function () {
    return view('collection.superdeal');
})->name('collection.superdeal');

Route::get('produk/bubuk-cokelat', function () {
    return view('product-detail');
});

Route::get('cart', function () {
    return view('cart');
})->name('cart');

Route::get('checkout', function () {
    return view('checkout');
})->name('checkout');

Route::get('payment', function () {
    return view('payment');
})->name('payment');

Route::get('store/631a5d56aa3096cbda26050f', function () {
    return view('store');
});

Route::get('dashboard/profile', function () {
    return view('dashboard.profile');
})->name('template.dashboard.profile');

Route::get('dashboard/address', function () {
    return view('dashboard.address');
})->name('template.dashboard.address');

Route::get('dashboard/payment', function () {
    return view('dashboard.payment');
})->name('template.dashboard.payment');

Route::get('dashboard/transactions', function () {
    return view('dashboard.transactions');
})->name('template.dashboard.transactions');

Route::get('login-as', function () {
    return view('auth.login-as');
})->name('login.as');

Route::get('login', function () {
    return view('auth.login');
})->name('template.login');

Route::get('register-as', function () {
    return view('auth.register-as');
})->name('register.as');

Route::get('register', function (Request $request) {
    $role = $request->query('role');

    if ($role === 'seller') {
        return view('auth.register-seller');
    }

    return view('auth.register');
})->name('template.register');
