<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique(); // Auto-generated ID (used as barcode)
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('age')->nullable();
            $table->enum('age_type', ['year', 'month', 'day'])->default('year');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('allergies')->nullable();
            $table->string('avatar')->nullable();
            $table->date('last_visit')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
