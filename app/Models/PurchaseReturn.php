<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_number', 'purchase_id', 'vendor_id', 'return_date',
        'total', 'reason', 'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
