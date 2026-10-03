<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * balance_usd : solde disponible (récompenses validées − retraits demandés ou payés).
 * pending_usd : retraits demandés, en cours de paiement par l'équipe.
 */
class Wallet extends Model
{
    public const MIN_PAYOUT_USD = 5;

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
            ->where('type', Transaction::TYPE_REWARD)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount_usd');
    }

    public function totalPaidOut(): float
    {
        return (float) $this->transactions()
            ->where('type', Transaction::TYPE_PAYOUT)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount_usd');
    }

    /** Récompenses des participations soumises, pas encore validées. */
    public function rewardsAwaitingReview(): float
    {
        return $this->user->participations()
            ->with(['mission', 'user.socialNetworks'])
            ->whereIn('status', [Participation::STATUS_SUBMITTED, Participation::STATUS_UNDER_REVIEW])
            ->get()
            ->sum(fn (Participation $participation) => $participation->rewardAmount());
    }

    public function hasPendingPayout(): bool
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_PAYOUT)
            ->where('status', Transaction::STATUS_PENDING)
            ->exists();
    }
}
