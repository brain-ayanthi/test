<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number', 'sale_date', 'prescription_id', 'patient_id', 'doctor_id',
        'customer_type', 'customer_name', 'customer_phone',
        'subtotal', 'discount', 'tax', 'doctor_fee', 'total',
        'paid_amount', 'due_amount', 'change_amount',
        'payment_status', 'payment_method', 'cash_account_id',
        'notes', 'created_by',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'doctor_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($sale) {
            if (empty($sale->invoice_number)) {
                $sale->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last = static::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $seq = $last ? ((int) substr($last->invoice_number, -5)) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }
}
