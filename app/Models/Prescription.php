<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = [
        'prescription_number', 'patient_id', 'doctor_id', 'ready_treatment_id',
        'prescription_date', 'status', 'sale_status',
        'patient_name', 'patient_age', 'patient_phone', 'patient_code',
        'medicine_cost', 'doctor_fee', 'radiology_cost', 'discount', 'tax', 'total_fee',
        'paid_amount', 'due_amount', 'payment_status', 'payment_method',
        'diagnosis', 'lab_workup', 'precautions', 'physiotherapy', 'notes', 'next_visit',
        'is_printed', 'created_by',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'medicine_cost' => 'float',
        'doctor_fee' => 'float',
        'radiology_cost' => 'float',
        'discount' => 'float',
        'tax' => 'float',
        'total_fee' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
        'is_printed' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($prescription) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'RX-' . now()->format('Ymd') . '-';
        // Daily sequence: 01, 02, 03... resets to 01 on a new day.
        $last = static::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();
        if ($last && preg_match('/-(\d{1,5})$/', $last->prescription_number, $m)) {
            $seq = ((int) $m[1]) + 1;
        } else {
            $seq = 1;
        }
        return $prefix . str_pad((string) $seq, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Preview the NEXT prescription number without creating a record.
     * Used by the create screen so the side panel and popup always match.
     */
    public static function previewNextNumber(): string
    {
        $prefix = 'RX-' . now()->format('Ymd') . '-';
        $last = static::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();
        if ($last && preg_match('/-(\d{1,5})$/', $last->prescription_number, $m)) {
            $seq = (int) $m[1];
        } else {
            $seq = 0;
        }
        return $prefix . str_pad((string) ($seq + 1), 2, '0', STR_PAD_LEFT);
    }

    // Primary (first) patient - kept for backward compatibility
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // All patients in this prescription
    public function prescriptionPatients()
    {
        return $this->hasMany(PrescriptionPatient::class)->orderBy('id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function readyTreatment()
    {
        return $this->belongsTo(ReadyTreatment::class);
    }

    // All items across all patients in this prescription
    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function documents()
    {
        return $this->hasMany(PrescriptionDocument::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Recalculate totals from all patients (medicine + doctor + radiology).
     */
    public function recalculateTotals(): void
    {
        $this->loadMissing('prescriptionPatients.items', 'prescriptionPatients.radiologies');

        $medicine = 0;
        $doctorFees = 0;
        $radiology = 0;
        $discount = 0;

        foreach ($this->prescriptionPatients as $pp) {
            $pp->recalculate();
            $medicine += (float) $pp->medicine_cost;
            $doctorFees += (float) $pp->doctor_fee;
            $radiology += (float) $pp->radiology_cost;
            $discount += (float) $pp->discount;
        }

        $this->medicine_cost = $medicine;
        $this->doctor_fee = $doctorFees;
        $this->radiology_cost = $radiology;
        $this->discount = $discount;
        $this->total_fee = $medicine + $doctorFees + $radiology - $discount + (float) $this->tax;
        $this->due_amount = max(0, $this->total_fee - (float) $this->paid_amount);
        $this->save();
    }
}
