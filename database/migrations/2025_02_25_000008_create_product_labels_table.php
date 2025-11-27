<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('type')->default('tag'); // tag, badge, compliance
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_labels');
    }
};
