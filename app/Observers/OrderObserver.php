<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Store;

class OrderObserver
{
    /**
     * Status yang dianggap sebagai transaksi selesai
     */
    protected array $completedStatuses = ['completed', 'delivered'];

    /**
     * Handle the Order "updated" event.
     * Update store transactions_count ketika status order berubah
     */
    public function updated(Order $order): void
    {
        // Cek jika status berubah
        if ($order->isDirty('status')) {
            $this->updateStoreTransactionsCount($order->store_id);
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Jika order dibuat dengan status completed/delivered langsung
        if (in_array($order->status, $this->completedStatuses)) {
            $this->updateStoreTransactionsCount($order->store_id);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $this->updateStoreTransactionsCount($order->store_id);
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
