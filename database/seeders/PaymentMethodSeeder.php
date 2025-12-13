<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'cod',
                'name' => 'COD (Cash on Delivery)',
                'channel' => 'cod',
                'metadata' => [
                    'description' => 'Bayar saat barang diterima',
                    'instructions' => 'Pembayaran dilakukan langsung kepada kurir saat menerima pesanan.',
                ],
                'is_active' => true,
            ],
            [
                'code' => 'manual_transfer',
                'name' => 'Transfer Manual',
                'channel' => 'manual_transfer',
                'metadata' => [
                    'description' => 'Transfer ke rekening toko',
                    'instructions' => 'Silakan lakukan transfer ke rekening toko yang tertera pada invoice. Upload bukti transfer melalui dashboard.',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
