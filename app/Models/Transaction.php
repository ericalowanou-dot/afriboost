<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'participation_id',
        'mission_id',
        'amount_usd',
        'type',
        'status',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function participation(): BelongsTo
    {
        return $this->belongsTo(Participation::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'reward_credit' => 'Paiement reçu',
            'reward_pending' => 'En attente',
            'payout' => 'Retrait',
            'adjustment' => 'Ajustement',
            default => $this->type,
        };
    }
}
