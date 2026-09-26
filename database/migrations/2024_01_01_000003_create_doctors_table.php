<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->string('qualification')->nullable();
            $table->string('chamber')->nullable();
            $table->decimal('visit_fee', 12, 2)->default(0);
            $table->decimal('doctor_fee', 12, 2)->default(0);
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
