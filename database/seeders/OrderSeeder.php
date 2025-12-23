<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
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
        $stores = Store::where('is_verified', true)->get();
        $products = Product::all();

        if ($customers->isEmpty() || $stores->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Customers, Stores, or Products are empty. Please run necessary seeders first.');
            return;
        }

        // Order statuses with weights for realistic distribution
        $statuses = [
            'pending_payment' => 10,
            'processing' => 20,
            'shipped' => 25,
            'delivered' => 15,
            'completed' => 25,
            'cancelled' => 5,
        ];

        $paymentStatuses = [
            'pending' => 20,
            'paid' => 80,
        ];

        // Create sample orders - more data for realistic dashboard
        $orderCount = 50;
        $existingCount = Order::count();

        for ($i = 1; $i <= $orderCount; $i++) {
            $customer = $customers->random();
            $store = $stores->random();

            // Random date within last 30 days
            $orderDate = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $status = $this->weightedRandom($statuses);
            $paymentStatus = $this->weightedRandom($paymentStatuses);

            // Cancelled orders should have pending payment
            if ($status === 'cancelled') {
                $paymentStatus = 'pending';
            }

            // Completed orders must be paid
            if (in_array($status, ['completed', 'delivered', 'shipped'])) {
                $paymentStatus = 'paid';
            }

            // Generate unique order number
            $orderNumber = 'ORD-' . $orderDate->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $order = Order::create([
                'user_id' => $customer->id,
                'store_id' => $store->id,
                'order_number' => $orderNumber,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'subtotal' => 0,
                'shipping_cost' => rand(10000, 50000),
                'grand_total' => 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add order items (1-4 items per order)
            $itemCount = rand(1, 4);
            $subtotal = 0;

            // Get products from the same store if possible, otherwise random
            $storeProducts = $products->where('store_id', $store->id);
            $availableProducts = $storeProducts->isNotEmpty() ? $storeProducts : $products;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $availableProducts->random();
                $quantity = rand(1, 5);
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

        $this->command->info("Created {$orderCount} orders with varied statuses and dates.");
    }

    /**
     * Select a random item based on weights
     */
    private function weightedRandom(array $weightedItems): string
    {
        $totalWeight = array_sum($weightedItems);
        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($weightedItems as $item => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $item;
            }
        }

        return array_key_first($weightedItems);
    }
}
