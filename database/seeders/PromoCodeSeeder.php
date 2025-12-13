<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'WELCOME10',
                'description' => 'Diskon 10% untuk member baru',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_order_amount' => 100000,
                'max_discount' => 50000,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(3),
                'quota' => 100,
                'used' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'FLASHSALE50',
                'description' => 'Potongan Rp 50.000',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 200000,
                'max_discount' => null,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'quota' => 50,
                'used' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'description' => 'Gratis ongkir Rp 25.000',
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'min_order_amount' => 150000,
                'max_discount' => null,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(6),
                'quota' => null,
                'used' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($promoCodes as $promo) {
            PromoCode::updateOrCreate(
                ['code' => $promo['code']],
                $promo
            );
        }
    }
}
