<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->date('purchase_date')->index();
            $table->date('due_date')->nullable();
            $table->string('reference')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('shipping', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);
            // payment_status: paid, partial, pending
            $table->enum('payment_status', ['paid', 'partial', 'pending'])->default('pending');
            $table->enum('payment_method', ['cash', 'bank', 'mfs', 'cheque'])->default('cash');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            // Quantity purchased in purchase unit (e.g. 5 boxes)
            $table->decimal('purchase_quantity', 12, 2)->default(0);
            $table->foreignId('purchase_unit_id')->nullable()->constrained('units')->nullOnDelete();
            // Conversion to base pieces (e.g. 1 box = 1000 pieces)
            $table->decimal('pieces_per_unit', 12, 2)->default(1);
            // Total pieces = purchase_quantity * pieces_per_unit
            $table->decimal('total_pieces', 12, 2)->default(0);

            $table->decimal('purchase_price', 12, 2)->default(0); // per purchase unit
            $table->decimal('unit_cost', 12, 4)->default(0);      // cost per piece
            $table->decimal('selling_price', 12, 2)->default(0);  // per piece

            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'bank', 'mfs', 'cheque'])->default('cash');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->date('return_date');
            $table->decimal('total', 14, 2)->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
