<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('shipping_pickup')->default(false)->after('is_tkdn');
            $table->boolean('shipping_delivery')->default(false)->after('shipping_pickup');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shipping_pickup', 'shipping_delivery']);
        });
    }
};
