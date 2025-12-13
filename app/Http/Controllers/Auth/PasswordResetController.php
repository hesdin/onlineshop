<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    public function show(Request $request, int $id, string $hash): Response
    {
        $user = User::findOrFail($id);

        if (! $request->hasValidSignature() || ! hash_equals($hash, sha1($user->email))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Email belum terverifikasi.');
        }

        return Inertia::render('Auth/SetPassword', [
            'signedUrl' => $request->fullUrl(),
            'mode' => 'reset',
        ]);
    }

    public function update(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! $request->hasValidSignature() || ! hash_equals($hash, sha1($user->email))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Email belum terverifikasi.');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'password_changed_at' => now(),
        ])->save();

        return redirect()->route($this->loginRouteName($user))->with('success', 'Password berhasil diubah. Silakan login.');
    }

    private function loginRouteName(User $user): string
    {
        if ($user->hasRole('superadmin')) {
            return 'login';
        }

        if ($user->hasRole('seller')) {
            return 'seller.login';
        }

        if ($user->hasRole('customer')) {
            return 'customer.login';
        }

        return 'portal.login';
    }
}
