<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_services', function (Blueprint $table) {
            $table->id();
            $table->string('courier');      // misal: jne, tiki, pos
            $table->string('code');         // misal: reg, yes, oke
            $table->string('label');        // misal: Reguler, YES
            $table->decimal('cost', 12, 2); // biaya pengiriman
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_services');
    }
};
