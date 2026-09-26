<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionPatientRadiology extends Model
{
    protected $table = 'prescription_patient_radiology';

    protected $fillable = [
        'prescription_patient_id', 'radiology_test_id',
        'test_name', 'price', 'notes',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function prescriptionPatient()
    {
        return $this->belongsTo(PrescriptionPatient::class);
    }

    public function test()
    {
        return $this->belongsTo(RadiologyTest::class, 'radiology_test_id');
    }
}
