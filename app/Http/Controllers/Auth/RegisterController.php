<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyAndSetPasswordMail;
use App\Models\SellerDocument;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function storeCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:50', Rule::unique('user_profiles', 'phone')],
            'referral' => ['nullable', 'string', 'max:255'],
            'g-recaptcha-response' => ['required', 'recaptchav3:register_customer,0.5'],
            'agree' => ['accepted'],
        ], [
            'email.unique' => 'Email sudah terdaftar. Silakan login atau gunakan lupa password.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'g-recaptcha-response.required' => 'Silakan verifikasi reCAPTCHA.',
            'g-recaptcha-response.recaptchav3' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        DB::transaction(function () use ($data, $customerRole): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Str::random(20),
            ]);

            $user->assignRole($customerRole);

            $user->profile()->create([
                'phone' => $data['phone'],
                'referrer_code' => $data['referral'] ? trim($data['referral']) : null,
                'accepted_terms_at' => now(),
            ]);

            // Send email inside transaction - will rollback if email fails
            Mail::to($user)->queue(new VerifyAndSetPasswordMail($user, 'customer_self'));
        });

        return back()->with('success', 'Registrasi berhasil. Cek email Anda.');
    }

    public function storeSeller(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:50', Rule::unique('user_profiles', 'phone')],
            'referral' => ['nullable', 'string', 'max:255'],
            'g-recaptcha-response' => ['required', 'recaptchav3:register_seller,0.5'],
            'agree' => ['accepted'],
        ], [
            'email.unique' => 'Email sudah terdaftar. Silakan login atau gunakan lupa password.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'g-recaptcha-response.required' => 'Silakan verifikasi reCAPTCHA.',
            'g-recaptcha-response.recaptchav3' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        $sellerRole = Role::firstOrCreate(['name' => 'seller']);

        DB::transaction(function () use ($data, $sellerRole): void {
            // Create user
            $user = User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => Str::random(20),
            ]);

            $user->assignRole($sellerRole);

            // Create profile
            $user->profile()->create([
                'phone' => $data['phone'],
                'referrer_code' => $data['referral'] ? trim($data['referral']) : null,
                'accepted_terms_at' => now(),
            ]);

            // Create store with temporary name/slug
            $tempName = 'Toko ' . $user->name;
            $slug = Str::slug($tempName) . '-' . Str::random(6);

            $store = Store::create([
                'user_id' => $user->id,
                'name' => $tempName,
                'slug' => $slug,
                'is_verified' => false,
                'is_umkm' => true,
            ]);

            // Create seller document record
            SellerDocument::create([
                'store_id' => $store->id,
                'submission_status' => 'draft',
            ]);

            // Send email inside transaction - will rollback if email fails
            Mail::to($user)->queue(new VerifyAndSetPasswordMail($user, 'seller_self'));
        });

        return back()->with('success', 'Registrasi berhasil. Silakan cek email Anda untuk verifikasi dan melengkapi data usaha.');
    }
}
