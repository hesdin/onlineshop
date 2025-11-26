<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('index');
});

Route::get('/kategori', function () {
    return view('category');
});

Route::get('/produk/bubuk-cokelat', function () {
    return view('product-detail');
});

Route::get('/login-as', function () {
    return view('auth.login-as');
})->name('login.as');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register-as', function () {
    return view('auth.register-as');
})->name('register.as');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');
