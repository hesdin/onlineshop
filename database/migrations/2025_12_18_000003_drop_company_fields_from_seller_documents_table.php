<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seller_documents')) {
            return;
        }

        Schema::table('seller_documents', function (Blueprint $table) {
            if (Schema::hasColumn('seller_documents', 'company_name')) {
                $table->dropColumn('company_name');
            }

            if (Schema::hasColumn('seller_documents', 'company_type')) {
                $table->dropColumn('company_type');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('seller_documents')) {
            return;
        }

        Schema::table('seller_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('seller_documents', 'company_name')) {
                $table->string('company_name')->nullable();
            }

            if (! Schema::hasColumn('seller_documents', 'company_type')) {
                $table->string('company_type')->nullable();
            }
        });
    }
};

