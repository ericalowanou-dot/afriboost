<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance_usd',
        'pending_usd',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'balance_usd' => 'decimal:2',
            'pending_usd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function totalEarned(): float
    {
        return (float) $this->transactions()
            ->where('type', 'reward_credit')
            ->where('status', 'completed')
            ->sum('amount_usd');
    }
}
