<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            // Document statuses
            $table->string('ktp_status')->default('pending'); // pending, approved, rejected
            $table->string('npwp_status')->default('pending');
            $table->string('nib_status')->default('pending');

            // Overall submission status
            $table->string('submission_status')->default('draft'); // draft, submitted, approved, rejected

            // Admin review
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_documents');
    }
};
