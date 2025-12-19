<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        return Inertia::render('Seller/Profile/Index', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $profile->phone,
                'avatar_url' => $this->formatMediaUrl($user->getFirstMediaUrl('profile_image')),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();
        $user->update(['name' => $data['name']]);

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
        $profile->update([
            'phone' => $data['phone'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('avatar')->toMediaCollection('profile_image');
        }

        return Redirect::route('seller.profile.show')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($data['password']),
            'password_changed_at' => now(),
        ])->save();

        return Redirect::route('seller.profile.show')->with('success', 'Kata sandi berhasil diperbarui.');
    }

    private function formatMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }
}
