<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Multi-account balance tracking: Store Cash, Bank, MFS, etc.
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // Store Cash, Bank, MFS (bKash/Nagad)
            $table->string('type');          // cash, bank, mfs
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_account_id')->constrained()->cascadeOnDelete();
            // income / expense / transfer_in / transfer_out
            $table->enum('type', ['income', 'expense', 'transfer_in', 'transfer_out']);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2)->default(0);
            // Polymorphic link to source (sale, purchase, prescription, etc.)
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->date('transaction_date')->index();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });

        // Transfers between accounts
        Schema::create('cash_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->constrained('cash_accounts');
            $table->foreignId('to_account_id')->constrained('cash_accounts');
            $table->decimal('amount', 14, 2);
            $table->date('transfer_date');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transfers');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cash_accounts');
    }
};
