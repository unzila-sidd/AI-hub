<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['cash', 'card', 'upi', 'bank_transfer', 'other'];

    protected $fillable = [
        'sale_id',
        'user_id',
        'amount',
        'method',
        'reference',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}