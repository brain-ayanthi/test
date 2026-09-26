<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id', 'batch_number', 'manufacturing_date', 'expiry_date',
        'purchase_price', 'selling_price', 'quantity', 'initial_quantity',
        'rack_number', 'purchase_id', 'purchase_item_id',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'initial_quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return !$this->isExpired() && $this->expiry_date->lte(now()->addDays($days));
    }
}
