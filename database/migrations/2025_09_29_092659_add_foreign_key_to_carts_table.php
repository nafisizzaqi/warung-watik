<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Tambahin foreign key kalau kolomnya sudah ada
            if (Schema::hasColumn('carts', 'customer_id')) {
                $table->foreign('customer_id')
                      ->references('id')
                      ->on('customers')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Hapus foreign key aja (kolom tetap ada)
            $table->dropForeign(['customer_id']);
        });
    }
};
