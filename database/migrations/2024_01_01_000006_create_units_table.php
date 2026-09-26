<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Units like Box, Packet, Strip, Piece
        // A box may contain 1000 pieces, a packet 100 pieces, etc.
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // e.g. Box, Packet, Strip, Piece
            $table->string('short_name');   // e.g. box, pkt, strip, pc
            $table->decimal('conversion', 12, 2)->default(1); // pieces per unit (Box=1000)
            $table->boolean('is_base_unit')->default(false);  // Piece = base unit
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
