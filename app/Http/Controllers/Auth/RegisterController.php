<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyAndSetPasswordMail;
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
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        /** @var \App\Models\User $user */
        $user = DB::transaction(function () use ($data, $customerRole): User {
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

            return $user;
        });

        Mail::to($user)->send(new VerifyAndSetPasswordMail($user, 'customer_self'));

        return back()->with('success', 'Registrasi berhasil. Cek email untuk verifikasi dan buat password baru.');
    }

    public function storeSeller(Request $request): RedirectResponse
    {
        $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:50'],
            'referral' => ['nullable', 'string', 'max:255'],
            'g-recaptcha-response' => ['required', 'recaptchav3:register_seller,0.5'],
        ]);

        return back()->with('success', 'Registrasi seller berhasil diverifikasi.');
    }
}
