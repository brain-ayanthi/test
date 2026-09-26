<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = [
        'purchase_id', 'payment_date', 'amount', 'method',
        'reference', 'notes', 'account_id', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function account()
    {
        return $this->belongsTo(CashAccount::class, 'account_id');
    }
}
