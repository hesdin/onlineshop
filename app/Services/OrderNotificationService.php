<?php

namespace App\Services;

use App\Events\NotificationReceived;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\ReviewRequestNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

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
            Notification::sendNow($order->user, new OrderStatusChangedNotification(
                $order,
                $newStatus,
                $newPaymentStatus
            ));

            // Dispatch realtime event for customer
            $this->dispatchRealtimeNotification($order->user);

            // Send review request if order completed
            if ($newStatus === 'completed' && $previousStatus !== 'completed') {
                Notification::sendNow($order->user, new ReviewRequestNotification($order));
                // Dispatch realtime event for review request
                $this->dispatchRealtimeNotification($order->user);
            }
        }

        // Skip email if customer has no email
        $customerEmail = $order->user?->email;
        if (! $customerEmail) {
            Log::warning("Cannot send order status email: Order #{$order->order_number} has no customer email");

            return;
        }

        try {
            Mail::to($customerEmail)->queue(new OrderStatusChangedMail(
                order: $order,
                previousStatus: $previousStatus,
                newStatus: $newStatus,
                previousPaymentStatus: $previousPaymentStatus,
                newPaymentStatus: $newPaymentStatus,
            ));

            Log::info('Order status notification sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $customerEmail,
                'status_change' => "{$previousStatus} -> {$newStatus}",
                'payment_status_change' => $previousPaymentStatus && $newPaymentStatus
                    ? "{$previousPaymentStatus} -> {$newPaymentStatus}"
                    : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispatch realtime notification event for the given user.
     */
    private function dispatchRealtimeNotification($user): void
    {
        $notification = $user->notifications()->latest()->first();
        if ($notification) {
            event(new NotificationReceived($user, [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'title' => $notification->data['title'] ?? 'Notifikasi',
                'message' => $notification->data['message'] ?? '',
                'icon' => $notification->data['icon'] ?? 'bell',
                'action_url' => $notification->data['action_url'] ?? null,
                'read_at' => null,
                'created_at' => $notification->created_at->diffForHumans(),
            ]));
        }
    }
}

