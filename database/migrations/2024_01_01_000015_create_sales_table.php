<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Standalone and linked invoicing system
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('sale_date')->index();

            // A sale can be linked to a prescription (prescription-to-sale conversion)
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();

            // customer_type: patient / walking / customer
            $table->string('customer_type')->default('walking');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('doctor_fee', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);
            $table->decimal('change_amount', 14, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'pending'])->default('pending');
            $table->enum('payment_method', ['cash', 'bank', 'mfs'])->default('cash');
            $table->foreignId('cash_account_id')->nullable()->constrained()->nullOnDelete();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->foreignId('prescription_item_id')->nullable()->constrained('prescription_items')->nullOnDelete();

            $table->string('product_name');
            $table->string('batch_number')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'bank', 'mfs'])->default('cash');
            $table->foreignId('cash_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Returns / refunds
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->date('return_date');
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->enum('refund_method', ['cash', 'bank', 'mfs'])->default('cash');
            $table->foreignId('cash_account_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
