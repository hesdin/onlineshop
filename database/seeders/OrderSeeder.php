<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get customer user
        $customer = User::role('customer')->first();
        $store = Store::where('slug', 'toko-elektronik-jaya')->first();
        $products = Product::where('store_id', $store?->id)->get();

        if (! $customer || ! $store || $products->isEmpty()) {
            $this->command?->warn('Customer, Store, or Products are empty. Please run necessary seeders first.');

            return;
        }

        // Create 3 sample orders with different statuses
        $orderData = [
            [
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'days_ago' => 0,
            ],
            [
                'status' => 'processing',
                'payment_status' => 'paid',
                'days_ago' => 2,
            ],
            [
                'status' => 'completed',
                'payment_status' => 'paid',
                'days_ago' => 7,
            ],
        ];

        foreach ($orderData as $index => $data) {
            $orderDate = Carbon::now()->subDays($data['days_ago']);
            $orderNumber = 'ORD-'.$orderDate->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));

            $order = Order::create([
                'user_id' => $customer->id,
                'store_id' => $store->id,
                'order_number' => $orderNumber,
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'subtotal' => 0,
                'shipping_cost' => 15000,
                'grand_total' => 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add 1-2 random products to each order
            $itemCount = rand(1, 2);
            $subtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 2);
                $unitPrice = $product->sale_price ?? $product->price;
                $itemSubtotal = $unitPrice * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                $subtotal += $itemSubtotal;
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'grand_total' => $subtotal + $order->shipping_cost,
            ]);
        }

        $this->command?->info('OrderSeeder: 3 sample orders berhasil dibuat.');
    }
}
