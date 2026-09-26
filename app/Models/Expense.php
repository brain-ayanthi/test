<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id', 'expense_date', 'title', 'amount',
        'payment_method', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
