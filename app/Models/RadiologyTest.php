<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiologyTest extends Model
{
    protected $fillable = [
        'name', 'code', 'price', 'category', 'description',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
