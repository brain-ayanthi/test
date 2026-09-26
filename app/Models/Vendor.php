<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name', 'company', 'email', 'phone', 'address',
        'tax_number', 'opening_balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'is_active' => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    /**
     * Total purchase amount (all purchases).
     */
    public function getTotalPurchasesAttribute(): float
    {
        return (float) $this->purchases()->sum('total');
    }

    /**
     * Total amount paid to this vendor.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Outstanding balance = (opening balance + total purchases) - total paid.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->opening_balance
             + (float) $this->purchases()->sum('total')
             - (float) $this->payments()->sum('amount');
    }
}
