<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('purchase_request_number')->nullable();
            $table->string('po_number')->nullable();
            $table->string('order_type')->default('retail'); // retail / b2b
            $table->string('status')->default('pending_payment'); // pending_payment, processing, shipped, delivered, completed, refund, other
            $table->string('payment_status')->default('pending'); // pending, paid, expired, failed
            $table->string('payment_term')->default('immediate'); // immediate, termin_30, termin_90, termin_120, termin_150, termin_180
            $table->string('benefit')->nullable(); // Gratis Ongkir, Custom, etc.
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('weight_total')->default(0); // grams
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('shipping_service')->nullable();
            $table->string('shipping_awb')->nullable();
            $table->string('shipping_eta')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
