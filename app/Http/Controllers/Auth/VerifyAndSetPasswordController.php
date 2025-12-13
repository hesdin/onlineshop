<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class VerifyAndSetPasswordController extends Controller
{
    public function show(Request $request, int $id, string $hash): Response
    {
        $user = User::findOrFail($id);

        if (! $request->hasValidSignature() || ! hash_equals($hash, sha1($user->email))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return Inertia::render('Auth/SetPassword', [
            'signedUrl' => $request->fullUrl(),
            'mode' => 'verify',
        ]);
    }

    public function update(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->email)) || ! $request->hasValidSignature()) {
            abort(403);
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'password_changed_at' => now(),
        ])->save();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route($this->loginRouteName($user))->with('success', 'Password berhasil diubah. Silakan login.');
    }

    public function forceForm(): Response
    {
        return Inertia::render('Auth/SetPassword', [
            'signedUrl' => null,
            'mode' => 'force',
        ]);
    }

    public function forceUpdate(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'password_changed_at' => now(),
        ])->save();

        return redirect()->intended($this->redirectPath($user))
            ->with('success', 'Password berhasil diubah.');
    }

    private function redirectPath(User $user): string
    {
        if ($user->hasRole('superadmin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('seller')) {
            return route('seller.dashboard.index');
        }

        if ($user->hasRole('customer')) {
            return route('customer.dashboard.profile');
        }

        return route('home');
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
