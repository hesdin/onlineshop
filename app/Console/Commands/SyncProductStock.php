<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize product stock based on active orders';

    /**
     * Status order yang dianggap aktif (stok sudah digunakan)
     */
    protected array $activeStatuses = ['pending_payment', 'processing', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Calculating stock adjustments based on active orders...');

        // Get all products
        $products = Product::all();

        $changes = [];

        foreach ($products as $product) {
            // Calculate total quantity ordered in active orders
            $orderedQuantity = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $product->id)
                ->whereIn('orders.status', $this->activeStatuses)
                ->sum('order_items.quantity');

            // Get current stock from database (original stock before any deduction)
            // We need to figure out what the original stock was
            // For now, we'll show current stock and ordered quantity
            $currentStock = $product->stock;

            // Calculate expected stock based on assumption original stock was current + ordered
            // This is a one-time fix, so we need manual input for original stock
            $this->line("Product ID: {$product->id}");
            $this->line("  Name: {$product->name}");
            $this->line("  Current Stock: {$currentStock}");
            $this->line("  Total Ordered (active): {$orderedQuantity}");

            if ($orderedQuantity > 0) {
                $changes[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'current_stock' => $currentStock,
                    'ordered_qty' => $orderedQuantity,
                ];
            }
            $this->newLine();
        }

        if (empty($changes)) {
            $this->info('✅ No stock adjustments needed.');
            return Command::SUCCESS;
        }

        $this->table(
            ['Product ID', 'Name', 'Current Stock', 'Ordered Qty'],
            collect($changes)->map(fn ($c) => [$c['id'], substr($c['name'], 0, 30), $c['current_stock'], $c['ordered_qty']])->toArray()
        );

        $this->newLine();
        $this->warn('⚠️  Please review the data above.');
        $this->info('The Observer is now active. New orders will automatically reduce stock.');
        $this->info('For existing orders, you may need to manually adjust stock if needed.');

        return Command::SUCCESS;
    }
}
