<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyAndSetPasswordMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SellerSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/SellerLogin');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $request->user()->hasVerifiedEmail()) {
            Mail::to($request->user())->queue(new VerifyAndSetPasswordMail($request->user()));
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('Email belum terverifikasi. Cek email untuk verifikasi & setel password.'),
            ]);
        }

        if (! $request->user()->hasRole('seller')) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('Anda tidak memiliki akses sebagai seller.'),
            ]);
        }

        $request->session()->regenerate();

        if (is_null($request->user()->password_changed_at)) {
            return redirect()->route('password.force');
        }

        return redirect()->intended(route('seller.dashboard.index'));
    }
}
