<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opening_stock_imports', function (Blueprint $table) {
            // The importer always writes ID 1. Its existence permanently locks future imports.
            $table->unsignedBigInteger('id')->primary();
            $table->timestamp('imported_at');
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('products_count')->default(0);
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_stock_imports');
    }
};
