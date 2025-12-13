<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'address_id',
        'payment_method_id',
        'order_number',
        'purchase_request_number',
        'po_number',
        'order_type',
        'status',
        'payment_status',
        'payment_term',
        'benefit',
        'subtotal',
        'discount_total',
        'shipping_cost',
        'weight_total',
        'grand_total',
        'shipping_service',
        'shipping_awb',
        'shipping_eta',
        'ordered_at',
        'expires_at',
        'note',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getWhatsAppLink(): ?string
    {
        $phone = $this->store->phone ?? null;

        if (!$phone) {
            return null;
        }

        $formattedPhone = $this->formatPhoneNumber($phone);
        $message = urlencode($this->getWhatsAppMessage());

        return "https://wa.me/{$formattedPhone}?text={$message}";
    }

    public function getWhatsAppMessage(): string
    {
        $isCOD = $this->paymentMethod->code === 'cod';
        $storeName = $this->store->name;
        $orderNumber = $this->order_number;
        $date = $this->ordered_at?->format('d M Y H:i');
        $total = number_format($this->grand_total, 0, ',', '.');

        // Build items list
        $itemsList = $this->items->map(function ($item) {
            $subtotal = number_format($item->subtotal, 0, ',', '.');
            return "• {$item->name} x{$item->quantity} - Rp {$subtotal}";
        })->join("\n");

        if ($isCOD) {
            return <<<MSG
Halo {$storeName},

Saya sudah melakukan pemesanan:
📦 Order: {$orderNumber}
📅 Tanggal: {$date}

Produk:
{$itemsList}

💰 Total: Rp {$total}
🚚 Metode: COD (Cash on Delivery)

Mohon konfirmasi pesanan dan estimasi pengiriman.

Terima kasih!
MSG;
        } else {
            return <<<MSG
Halo {$storeName},

Saya sudah membuat pesanan:
📦 Order: {$orderNumber}
📅 Tanggal: {$date}

Produk:
{$itemsList}

💰 Total Pembayaran: Rp {$total}
💳 Metode: Transfer Manual

Mohon info rekening bank untuk pembayaran.

Terima kasih!
MSG;
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (Indonesia)
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . ltrim($phone, '0');
        }

        return $phone;
    }
}
