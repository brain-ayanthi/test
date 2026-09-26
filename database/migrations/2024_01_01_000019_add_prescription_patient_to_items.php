<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            // Link each medicine line to a specific patient within the prescription
            $table->foreignId('prescription_patient_id')
                  ->nullable()
                  ->after('prescription_id')
                  ->constrained('prescription_patients')
                  ->nullOnDelete();
            // Instruction / dosage note
            $table->text('instruction_note')->nullable()->after('instruction');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prescription_patient_id');
            $table->dropColumn('instruction_note');
        });
    }
};
