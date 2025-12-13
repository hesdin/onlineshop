<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get customer users
        $customerRole = Role::where('name', 'customer')->first();
        if (!$customerRole) {
            $this->command->warn('Customer role not found. Please run UserSeeder first.');
            return;
        }

        $customers = User::role('customer')->get();
        $stores = Store::all();
        $products = Product::all();

        if ($customers->isEmpty() || $stores->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Customers, Stores, or Products are empty. Please run necessary seeders first.');
            return;
        }

        // Create sample orders
        for ($i = 1; $i <= 10; $i++) {
            $customer = $customers->random();
            $store = $stores->random();

            $order = Order::create([
                'user_id' => $customer->id,
                'store_id' => $store->id,
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'status' => collect(['pending_payment', 'processing', 'shipped', 'delivered', 'completed'])->random(),
                'payment_status' => collect(['pending', 'paid'])->random(),
                'subtotal' => 0, // Will be calculated
                'shipping_cost' => rand(10000, 50000),
                'grand_total' => 0, // Will be calculated
            ]);

            // Add order items
            $itemCount = rand(1, 3);
            $subtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->sale_price ?? $product->price;
                $itemSubtotal = $unitPrice * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotal;
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'grand_total' => $subtotal + $order->shipping_cost,
            ]);
        }
    }
}
