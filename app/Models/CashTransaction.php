<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'cash_account_id', 'type', 'amount', 'balance_after',
        'source_type', 'source_id', 'reference', 'note',
        'transaction_date', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
