<?php

namespace App\Services;

use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\ReviewRequestNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    /**
     * Send notification when order status changes.
     */
    public function notifyStatusChanged(
        Order $order,
        string $previousStatus,
        string $newStatus,
        ?string $previousPaymentStatus = null,
        ?string $newPaymentStatus = null,
    ): void {
        // Skip if no actual change
        if ($previousStatus === $newStatus && $previousPaymentStatus === $newPaymentStatus) {
            return;
        }

        // Load required relationships
        $order->loadMissing(['store', 'items', 'user']);

        // Send database notification to customer bell icon
        if ($order->user) {
            $order->user->notify(new OrderStatusChangedNotification(
                $order,
                $newStatus,
                $newPaymentStatus
            ));

            // Send review request if order completed
            if ($newStatus === 'completed' && $previousStatus !== 'completed') {
                $order->user->notify(new ReviewRequestNotification($order));
            }
        }

        // Skip email if customer has no email
        $customerEmail = $order->user?->email;
        if (!$customerEmail) {
            Log::warning("Cannot send order status email: Order #{$order->order_number} has no customer email");
            return;
        }

        try {
            Mail::to($customerEmail)->send(new OrderStatusChangedMail(
                order: $order,
                previousStatus: $previousStatus,
                newStatus: $newStatus,
                previousPaymentStatus: $previousPaymentStatus,
                newPaymentStatus: $newPaymentStatus,
            ));

            Log::info("Order status notification sent", [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $customerEmail,
                'status_change' => "{$previousStatus} -> {$newStatus}",
                'payment_status_change' => $previousPaymentStatus && $newPaymentStatus
                    ? "{$previousPaymentStatus} -> {$newPaymentStatus}"
                    : null,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send order status notification", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

