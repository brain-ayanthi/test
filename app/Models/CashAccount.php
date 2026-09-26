<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    protected $fillable = [
        'name', 'type', 'account_number', 'bank_name',
        'opening_balance', 'balance', 'icon', 'color', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(CashTransaction::class, 'cash_account_id');
    }
}
