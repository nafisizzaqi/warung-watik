<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 1️⃣ Ubah user_id menjadi customer_id
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->renameColumn('user_id', 'customer_id');
            }

            // 2️⃣ Tambahkan kolom queue_number jika belum ada
            if (!Schema::hasColumn('orders', 'queue_number')) {
                $table->integer('queue_number')->nullable()->after('order_number');
            }
        });

        // 3️⃣ Update enum status
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('masuk', 'diproses', 'siap_ambil', 'selesai', 'batal') DEFAULT 'masuk'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'customer_id')) {
                $table->renameColumn('customer_id', 'user_id');
            }

            if (Schema::hasColumn('orders', 'queue_number')) {
                $table->dropColumn('queue_number');
            }
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled', 'shipped', 'completed') DEFAULT 'pending'");
    }
};
