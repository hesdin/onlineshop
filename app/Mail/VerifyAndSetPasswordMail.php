<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class VerifyAndSetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private User $user, private string $variant = 'default')
    {
    }

    public function build(): self
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.set-password',
            now()->addDays(3),
            [
                'id' => $this->user->id,
                'hash' => sha1($this->user->email),
            ]
        );

        return $this->subject('Verifikasi Email & Setel Password Akun')
            ->view('emails.verify-set-password')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $verificationUrl,
                'variant' => $this->variant,
            ]);
    }
}
