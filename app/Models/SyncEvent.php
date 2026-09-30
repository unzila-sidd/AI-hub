<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SyncEvent extends Model
{
    protected $fillable = [
        'entity',
        'entity_id',
        'action',
        'payload',
        'synced',
        'attempts',
        'last_error',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced' => 'boolean',
        'attempts' => 'integer',
    ];

    public function scopeUnsynced(Builder $query): Builder
    {
        return $query->where('synced', false);
    }
}