<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('drug_type_id')->nullable()->constrained()->nullOnDelete();

            // Form type: tablet, capsule, syrup, injection, cream, etc.
            $table->string('form_type')->nullable();
            // Strength: e.g. 500mg, 250mg/5ml
            $table->string('strength')->nullable();
            // Generic/chemical name
            $table->string('generic_name')->nullable();
            // Manufacturer
            $table->string('manufacturer')->nullable();

            $table->text('description')->nullable();

            // Purchase unit (e.g. Box) and purchase pieces per box
            $table->foreignId('purchase_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('pieces_per_purchase_unit', 12, 2)->default(1);

            // Selling unit (Piece / Strip etc.)
            $table->foreignId('selling_unit_id')->nullable()->constrained('units')->nullOnDelete();

            $table->decimal('purchase_price', 12, 2)->default(0); // per purchase unit
            $table->decimal('selling_price', 12, 2)->default(0);  // per selling unit (piece)
            $table->decimal('mrp', 12, 2)->default(0);            // maximum retail price

            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);

            // Rack / shelf location
            $table->string('rack_number')->nullable();
            $table->string('shelf_number')->nullable();

            // Stock alert thresholds (in base units / pieces)
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->boolean('is_prescription_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_batch')->default(true);
            $table->string('image')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
