<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique(); // date-wise serial with barcode
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ready_treatment_id')->nullable()->constrained('ready_treatments')->nullOnDelete();

            $table->date('prescription_date')->index();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            // sale status: unsold, partial, sold (prescription-to-sale conversion)
            $table->enum('sale_status', ['unsold', 'partial', 'sold'])->default('unsold');

            // Snapshot of patient info at time of prescription
            $table->string('patient_name')->nullable();
            $table->integer('patient_age')->nullable();
            $table->string('patient_phone')->nullable();
            $table->string('patient_code')->nullable();

            // Fees
            $table->decimal('medicine_cost', 12, 2)->default(0); // auto-calculated, not editable
            $table->decimal('doctor_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'pending'])->default('pending');
            $table->enum('payment_method', ['cash', 'bank', 'mfs'])->nullable();

            // Clinical notes
            $table->text('diagnosis')->nullable();
            $table->text('lab_workup')->nullable();
            $table->text('precautions')->nullable();
            $table->text('physiotherapy')->nullable();
            $table->text('notes')->nullable();
            $table->text('next_visit')->nullable();

            $table->boolean('is_printed')->default(false);
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();

            $table->string('drug_name');
            $table->string('form_type')->nullable();
            $table->string('strength')->nullable();

            // Timing: OD (1x), BD/BID (2x), TDS (3x), QID (4x) - per day
            $table->enum('timing', ['OD', 'BD', 'BID', 'TDS', 'QID'])->default('TDS');
            $table->integer('timing_multiplier')->default(3); // numeric form: 1,2,3,4
            $table->json('times_of_day')->nullable(); // morning, afternoon, evening, night
            $table->string('meal_relation')->nullable(); // before/after meal

            $table->integer('duration_days');       // number of days
            // Total quantity in pieces = timing_multiplier * duration_days
            $table->decimal('quantity', 12, 2);
            $table->string('instruction')->nullable(); // dosage instruction

            $table->decimal('unit_price', 12, 2)->default(0); // selling price per piece
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Stock handling
            $table->boolean('stock_deducted')->default(false);
            $table->timestamps();
        });

        // Ready Treatment templates
        Schema::create('ready_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('disease')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ready_treatment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ready_treatment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('drug_name');
            $table->string('form_type')->nullable();
            $table->string('strength')->nullable();
            $table->enum('timing', ['OD', 'BD', 'BID', 'TDS', 'QID'])->default('TDS');
            $table->integer('timing_multiplier')->default(3);
            $table->json('times_of_day')->nullable();
            $table->string('meal_relation')->nullable();
            $table->integer('duration_days');
            $table->string('instruction')->nullable();
            $table->timestamps();
        });

        // Prescription documents / medical reports
        Schema::create('prescription_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_documents');
        Schema::dropIfExists('ready_treatment_items');
        Schema::dropIfExists('ready_treatments');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
