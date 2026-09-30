<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'token',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new plaintext token, store its hash, and return the plaintext
     * exactly once. The plaintext value is never persisted.
     */
    public static function createToken(User $user, string $name = 'api'): string
    {
        $plain = Str::random(60);

        self::create([
            'user_id' => $user->id,
            'name' => $name,
            'token' => hash('sha256', $plain),
        ]);

        return $plain;
    }
}