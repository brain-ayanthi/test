<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_number', 'vendor_id', 'purchase_date', 'due_date', 'reference',
        'subtotal', 'discount', 'tax', 'shipping', 'total',
        'paid_amount', 'due_amount', 'payment_status', 'payment_method',
        'notes', 'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($purchase) {
            if (empty($purchase->invoice_number)) {
                $purchase->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'PI-' . now()->format('Ymd') . '-';
        $last = static::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $seq = $last ? ((int) substr($last->invoice_number, -5)) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
