<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'user_id') && !Schema::hasColumn('customer_id')) {
                $table->renameColumn('user_id', 'customer_id');
            }

            if (!Schema::hasColumn('orders', 'queue_number')) {
                $table->integer('queue_number')->nullable()->after('order_number');
            }

            if (!Schema::hasColumn('orders', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'midtrans_transaction_status')) {
                $table->string('midtrans_transaction_status')->nullable()->after('midtrans_order_id');
            }
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('masuk', 'diproses', 'siap_ambil', 'selesai', 'batal') DEFAULT 'masuk'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'customer_id') && !Schema::hasColumn('user_id')) {
                $table->renameColumn('customer_id', 'user_id');
            }

            if (Schema::hasColumn('orders', 'queue_number')) {
                $table->dropColumn('queue_number');
            }

            if (Schema::hasColumn('orders', 'midtrans_order_id')) {
                $table->dropColumn('midtrans_order_id');
            }

            if (Schema::hasColumn('orders', 'midtrans_transaction_status')) {
                $table->dropColumn('midtrans_transaction_status');
            }
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled', 'shipped', 'completed') DEFAULT 'pending'");
    }
};
