<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop foreign key lama
            $table->dropForeign(['user_id']);

            // Rename kolom
            $table->renameColumn('user_id', 'customer_id');

            // Tambahkan foreign key baru
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->renameColumn('customer_id', 'user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
