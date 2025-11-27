<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('umkm'); // umkm, vendor, koperasi, premium
            $table->string('tax_status')->default('non_pkp'); // pkp / non_pkp
            $table->string('bumn_partner')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('address_line')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_umkm')->default(true);
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('transactions_count')->default(0);
            $table->string('response_time_label')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
