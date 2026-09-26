<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'specialization', 'qualification',
        'chamber', 'visit_fee', 'doctor_fee', 'address', 'is_active', 'user_id',
    ];

    protected $casts = [
        'visit_fee' => 'decimal:2',
        'doctor_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function readyTreatments()
    {
        return $this->hasMany(ReadyTreatment::class);
    }
}
