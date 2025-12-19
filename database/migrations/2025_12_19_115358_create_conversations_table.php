<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // Ensure unique conversation per customer-store pair
            $table->unique(['customer_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
