<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Batch tracking with FEFO (First Expiry First Out)
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->index();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->index();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);

            // Quantity in base units (pieces). For a Box purchase of 1000 pieces, qty = 1000
            $table->decimal('quantity', 12, 2)->default(0);       // current stock
            $table->decimal('initial_quantity', 12, 2)->default(0);

            $table->string('rack_number')->nullable();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->unsignedBigInteger('purchase_item_id')->nullable();

            $table->timestamps();
            $table->index(['product_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
