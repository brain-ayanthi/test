<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_code', 'name', 'email', 'phone', 'age', 'age_type',
        'gender', 'address', 'dob', 'blood_group', 'medical_history',
        'allergies', 'avatar', 'last_visit', 'created_by',
    ];

    protected $casts = [
        'age' => 'integer',
        'dob' => 'date',
        'last_visit' => 'date',
    ];

    protected static function booted()
    {
        // Auto-generate patient code (used as barcode) if not provided
        static::creating(function ($patient) {
            if (empty($patient->patient_code)) {
                $patient->patient_code = static::generatePatientCode();
            }
        });
    }

    public static function generatePatientCode(): string
    {
        $prefix = 'P';
        $date = now()->format('Ymd');
        $last = static::withTrashed()
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        $sequence = $last ? ((int) substr($last->patient_code, -4)) + 1 : 1;
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class)->latest();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
