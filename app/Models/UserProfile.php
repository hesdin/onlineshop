<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'phone_verified_at',
        'referral_code',
        'referrer_code',
        'accepted_terms_at',
        'position',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'accepted_terms_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
