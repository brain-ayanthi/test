<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadyTreatment extends Model
{
    protected $fillable = [
        'name', 'disease', 'description', 'doctor_id', 'is_active', 'created_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function items()
    {
        return $this->hasMany(ReadyTreatmentItem::class);
    }
}
