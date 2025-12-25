<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ResetPasswordLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private User $user)
    {
    }

    public function build(): self
    {
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addHour(),
            [
                'id' => $this->user->id,
                'hash' => sha1($this->user->email),
            ]
        );

        return $this->subject('Reset Password Akun')
            ->view('emails.reset-password')
            ->with([
                'user' => $this->user,
                'resetUrl' => $resetUrl,
            ]);
    }
}
