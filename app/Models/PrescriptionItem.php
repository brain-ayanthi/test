<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id', 'prescription_patient_id', 'product_id', 'batch_id',
        'drug_name', 'form_type', 'strength',
        'timing', 'timing_multiplier', 'times_of_day',
        'meal_relation', 'duration_days', 'quantity',
        'instruction', 'instruction_note',
        'unit_price', 'discount', 'total', 'stock_deducted',
    ];

    protected $casts = [
        'times_of_day' => 'array',
        'duration_days' => 'integer',
        'timing_multiplier' => 'integer',
        'quantity' => 'float',
        'unit_price' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'stock_deducted' => 'boolean',
    ];

    public const TIMING_MAP = [
        'OD'   => 1,
        'BD'   => 2,
        'BID'  => 2,
        'TDS'  => 3,
        'QID'  => 4,
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            $multiplier = $item->timing_multiplier
                ?? (self::TIMING_MAP[$item->timing] ?? 1);
            $item->timing_multiplier = $multiplier;
            // Keep a strength-aware quantity supplied by the prescription builder
            // (e.g. 250mg/500mg = 0.5 tablet per dose, or 5ml per dose).
            if ($item->quantity === null || $item->quantity === '') {
                $item->quantity = $multiplier * (int) $item->duration_days;
            }
            $unitPrice = (float) $item->unit_price;
            $discount = (float) $item->discount;
            $item->total = ($unitPrice * (float) $item->quantity) - $discount;
        });
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function prescriptionPatient()
    {
        return $this->belongsTo(PrescriptionPatient::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
