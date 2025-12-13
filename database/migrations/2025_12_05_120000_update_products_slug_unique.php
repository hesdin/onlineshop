<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Hapus constraint unik lama (slug saja)
            $table->dropUnique('products_slug_unique');

            // Ganti dengan kombinasi store_id + slug + deleted_at agar slug unik per toko,
            // dan bisa dipakai ulang setelah soft delete
            $table->unique(['store_id', 'slug', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_store_id_slug_deleted_at_unique');
            $table->unique('slug');
        });
    }
};
