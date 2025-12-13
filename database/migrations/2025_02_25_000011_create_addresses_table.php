<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('recipient_name');
            $table->string('phone', 30);
            $table->foreignId('province_id')->nullable()->constrained(config('laravolt.indonesia.table_prefix').'provinces');
            $table->foreignId('city_id')->nullable()->constrained(config('laravolt.indonesia.table_prefix').'cities');
            $table->foreignId('district_id')->nullable()->constrained(config('laravolt.indonesia.table_prefix').'districts');
            $table->string('postal_code', 10)->nullable();
            $table->string('address_line');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
