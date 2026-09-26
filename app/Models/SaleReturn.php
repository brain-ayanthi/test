<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = [
        'return_number', 'sale_id', 'patient_id', 'return_date',
        'total', 'refund_amount', 'refund_method', 'cash_account_id',
        'reason', 'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
