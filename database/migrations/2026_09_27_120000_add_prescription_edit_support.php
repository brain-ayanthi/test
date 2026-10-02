<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // No inferred/backfilled allocations: historical batch data is incomplete.
        Schema::create('prescription_stock_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_item_id')->constrained('prescription_items')->restrictOnDelete();
            $table->foreignId('batch_id')->constrained('product_batches')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
            $table->unique(['prescription_item_id', 'batch_id'], 'rx_allocation_item_batch_unique');
        });
        Schema::create('prescription_edit_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->longText('before_data');
            $table->longText('after_data');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        // Backup these tables before rollback: allocation data cannot be reconstructed.
        Schema::dropIfExists('prescription_edit_audits');
        Schema::dropIfExists('prescription_stock_allocations');
    }
};
