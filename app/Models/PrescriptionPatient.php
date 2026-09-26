<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionPatient extends Model
{
    protected $fillable = [
        'prescription_id', 'patient_id', 'doctor_id',
        'patient_name', 'patient_age', 'patient_phone', 'patient_code',
        'diagnosis', 'precautions', 'next_visit',
        'medicine_cost', 'doctor_fee', 'radiology_cost', 'discount', 'total',
    ];

    protected $casts = [
        'medicine_cost' => 'float',
        'doctor_fee' => 'float',
        'radiology_cost' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_patient_id');
    }

    public function radiologies()
    {
        return $this->hasMany(PrescriptionPatientRadiology::class);
    }

    public function recalculate(): void
    {
        $medicine = $this->items()->sum('total');
        $radiology = $this->radiologies()->sum('price');
        $this->medicine_cost = $medicine;
        $this->radiology_cost = $radiology;
        $this->total = $medicine + $radiology + (float) $this->doctor_fee - (float) $this->discount;
        $this->save();
    }
}
