<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    protected $fillable = [
        'vendor_id', 'purchase_id', 'amount', 'payment_date',
        'payment_method', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
