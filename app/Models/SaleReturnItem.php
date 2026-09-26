<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id', 'sale_item_id', 'product_id', 'batch_id',
        'quantity', 'unit_price', 'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }
}
