<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'store_id',
        'code',
        'description',
        'discount_type', // percent, fixed
        'discount_value',
        'min_order_amount',
        'max_discount',
        'starts_at',
        'ends_at',
        'quota',
        'used',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
