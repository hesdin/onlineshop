<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->unsignedInteger('min_order')->default(1);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight')->default(0); // grams
            $table->decimal('length', 10, 2)->unsigned()->nullable();
            $table->decimal('width', 10, 2)->unsigned()->nullable();
            $table->decimal('height', 10, 2)->unsigned()->nullable();
            $table->string('item_type')->default('product'); // product / service
            $table->string('status')->default('ready'); // ready / pre_order / inactive
            $table->string('location_city')->nullable();
            $table->string('location_province')->nullable();
            $table->boolean('is_pdn')->default(false);
            $table->boolean('is_pkp')->default(false);
            $table->boolean('is_tkdn')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
