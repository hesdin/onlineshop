<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordLinkMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'Email tidak ditemukan.'])
                ->withInput();
        }

        if (is_null($user->email_verified_at)) {
            return back()
                ->withErrors(['email' => 'Email belum diverifikasi.'])
                ->withInput();
        }

        Mail::to($user)->queue(new ResetPasswordLinkMail($user));

        return back()->with('success', 'Jika email terdaftar dan terverifikasi, tautan reset telah dikirim.');
    }
}
