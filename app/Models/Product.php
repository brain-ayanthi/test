<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'barcode', 'category_id', 'drug_type_id',
        'form_type', 'strength', 'generic_name', 'manufacturer', 'description',
        'purchase_unit_id', 'pieces_per_purchase_unit', 'selling_unit_id',
        'purchase_price', 'selling_price', 'mrp',
        'tax_percent', 'discount_percent',
        'rack_number', 'shelf_number', 'min_stock',
        'is_prescription_required', 'is_active', 'track_batch', 'image',
    ];

    protected $casts = [
        'pieces_per_purchase_unit' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'is_prescription_required' => 'boolean',
        'is_active' => 'boolean',
        'track_batch' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function drugType()
    {
        return $this->belongsTo(DrugType::class);
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function sellingUnit()
    {
        return $this->belongsTo(Unit::class, 'selling_unit_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class)->orderBy('expiry_date', 'asc');
    }

    public function getStockQuantityAttribute(): float
    {
        return (float) $this->batches()
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', now())
            ->sum('quantity');
    }

    /**
     * Append stock_quantity to array/JSON output so the prescription
     * builder can display available quantity.
     */
    protected $appends = ['stock_quantity'];

    /**
     * FEFO - pick batches by earliest expiry that still have stock
     */
    public function getAvailableBatches()
    {
        return $this->batches()
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', now())
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public function getLowStockBatches()
    {
        return $this->batches()->where('quantity', '>', 0)->where('quantity', '<=', $this->min_stock)->get();
    }

    public function getExpiringBatches(int $days = 30)
    {
        return $this->batches()
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->get();
    }

    public function getExpiredBatches()
    {
        return $this->batches()->where('quantity', '>', 0)->where('expiry_date', '<', now())->get();
    }
}
