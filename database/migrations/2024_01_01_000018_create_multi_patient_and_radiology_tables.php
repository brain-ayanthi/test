<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-patient prescriptions: one prescription number can contain
 * multiple patients (each with their own medicines & doctor fee).
 */
return new class extends Migration {
    public function up(): void
    {
        // Each patient entry within a prescription
        Schema::create('prescription_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot
            $table->string('patient_name');
            $table->integer('patient_age')->nullable();
            $table->string('patient_phone')->nullable();
            $table->string('patient_code')->nullable();

            // Per-patient clinical data
            $table->text('diagnosis')->nullable();
            $table->text('precautions')->nullable();
            $table->text('next_visit')->nullable();

            // Per-patient fees
            $table->decimal('medicine_cost', 12, 2)->default(0);
            $table->decimal('doctor_fee', 12, 2)->default(0);
            $table->decimal('radiology_cost', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();

            $table->index('prescription_id');
            $table->index('patient_id');
        });

        // Radiology tests master (ECG, X-RAY, etc.)
        Schema::create('radiology_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('category')->nullable(); // Radiology, Cardiology, Lab, etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Radiology tests assigned per patient within a prescription
        Schema::create('prescription_patient_radiology', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('radiology_test_id')->nullable()->constrained()->nullOnDelete();
            $table->string('test_name');
            $table->decimal('price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_patient_radiology');
        Schema::dropIfExists('radiology_tests');
        Schema::dropIfExists('prescription_patients');
    }
};
