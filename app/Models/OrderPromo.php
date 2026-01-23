<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPromo extends Model
{
    protected $fillable = [
        'order_id',
        'promo_code_id',
        'discount_amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
