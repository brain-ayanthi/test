<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionDocument extends Model
{
    protected $fillable = [
        'prescription_id', 'title', 'file_path', 'file_type',
        'file_size', 'uploaded_by',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
