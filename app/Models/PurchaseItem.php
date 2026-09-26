<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 'product_id', 'purchase_quantity', 'purchase_unit_id',
        'pieces_per_unit', 'total_pieces', 'purchase_price', 'unit_cost',
        'selling_price', 'batch_number', 'expiry_date', 'discount', 'tax', 'total',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_quantity' => 'decimal:2',
        'pieces_per_unit' => 'decimal:2',
        'total_pieces' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'purchase_item_id');
    }
}
