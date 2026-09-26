<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash'); // cash, bank, mfs, cheque
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vendor_id', 'payment_date']);
            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};
