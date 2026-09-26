<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadyTreatmentItem extends Model
{
    protected $fillable = [
        'ready_treatment_id', 'product_id', 'drug_name', 'form_type', 'strength',
        'timing', 'timing_multiplier', 'times_of_day', 'meal_relation',
        'duration_days', 'instruction',
    ];

    protected $casts = [
        'times_of_day' => 'array',
        'duration_days' => 'integer',
        'timing_multiplier' => 'integer',
    ];

    public function readyTreatment()
    {
        return $this->belongsTo(ReadyTreatment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
