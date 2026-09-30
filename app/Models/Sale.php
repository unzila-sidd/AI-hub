<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_name',
        'cashier_id',
        'subtotal',
        'discount',
        'total',
        'payment_status',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public static function generateInvoiceNo(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }

    public function refreshPaymentStatus(): void
    {
        $paid = (float) $this->payments()->sum('amount');

        $this->update([
            'payment_status' => $paid >= $this->total ? 'paid' : 'unpaid',
        ]);
    }
}