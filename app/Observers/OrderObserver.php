<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Store;
use App\Notifications\LowStockNotification;

class OrderObserver
{
    /**
     * Status yang dianggap sebagai transaksi selesai
     */
    protected array $completedStatuses = ['completed', 'delivered'];

    /**
     * Status yang dianggap order masih aktif (stok sudah dikurangi)
     */
    protected array $activeStatuses = ['pending_payment', 'processing', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

    /**
     * Handle the Order "created" event.
     * Note: Stock reduction is handled in OrderController after order items are created
     */
    public function created(Order $order): void
    {
        // Jika order dibuat dengan status completed/delivered langsung
        if (in_array($order->status, $this->completedStatuses)) {
            $this->updateStoreTransactionsCount($order->store_id);
        }
    }

    /**
     * Handle the Order "updated" event.
     * Update store transactions_count ketika status order berubah
     */
    public function updated(Order $order): void
    {
        // Cek jika status berubah
        if ($order->isDirty('status')) {
            $previousStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            // Kembalikan stok jika order dibatalkan dari status aktif
            if ($newStatus === 'cancelled' && in_array($previousStatus, $this->activeStatuses)) {
                $this->restoreStock($order);
            }

            $this->updateStoreTransactionsCount($order->store_id);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Kembalikan stok jika order dihapus
        if (in_array($order->status, $this->activeStatuses)) {
            $this->restoreStock($order);
        }

        $this->updateStoreTransactionsCount($order->store_id);
    }

    /**
     * Kurangi stok produk berdasarkan item dalam order
     */
    protected function reduceStock(Order $order): void
    {
        $order->load('items.product.store.user');

        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock !== null) {
                $item->product->decrement('stock', $item->quantity);

                // Refresh untuk mendapatkan stok terbaru
                $item->product->refresh();

                // Kirim notifikasi low stock jika stok <= 10
                if ($item->product->stock <= 10 && $item->product->stock >= 0) {
                    $this->notifyLowStock($item->product);
                }
            }
        }
    }

    /**
     * Kembalikan stok produk saat order dibatalkan
     */
    protected function restoreStock(Order $order): void
    {
        $order->load('items.product');

        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock !== null) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Kirim notifikasi low stock ke seller
     */
    protected function notifyLowStock($product): void
    {
        if ($product->store && $product->store->user) {
            $product->store->user->notify(new LowStockNotification(
                $product->name,
                $product->stock,
                $product->id
            ));
        }
    }

    /**
     * Update transactions_count pada store berdasarkan jumlah order yang selesai
     */
    protected function updateStoreTransactionsCount(?int $storeId): void
    {
        if (!$storeId) {
            return;
        }

        // Hitung jumlah order dengan status completed atau delivered
        $completedCount = Order::where('store_id', $storeId)
            ->whereIn('status', $this->completedStatuses)
            ->count();

        // Update store transactions_count
        Store::where('id', $storeId)->update([
            'transactions_count' => $completedCount,
        ]);
    }
}
