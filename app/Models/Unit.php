<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'short_name', 'conversion', 'is_base_unit', 'base_unit_id', 'is_active'];

    protected $casts = [
        'conversion' => 'decimal:2',
        'is_base_unit' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }
}
