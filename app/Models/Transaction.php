<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public const TYPE_REWARD = 'reward_credit';

    public const TYPE_PAYOUT = 'payout';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYOUT_METHODS = [
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Virement bancaire',
        'paypal' => 'PayPal',
    ];

    protected $fillable = [
        'user_id',
        'wallet_id',
        'participation_id',
        'mission_id',
        'amount_usd',
        'type',
        'status',
        'label',
        'payout_method',
        'payout_account',
        'admin_note',
        'processed_at',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'processed_at' => 'datetime',
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

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPayout(): bool
    {
        return $this->type === self::TYPE_PAYOUT;
    }

    public function isDebit(): bool
    {
        return $this->isPayout() || (float) $this->amount_usd < 0;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_REWARD => 'Récompense',
            'reward_pending' => 'En attente',
            self::TYPE_PAYOUT => 'Retrait',
            self::TYPE_ADJUSTMENT => 'Ajustement',
            default => $this->type,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => $this->isPayout() ? 'Paiement en cours' : 'En attente',
            self::STATUS_COMPLETED => $this->isPayout() ? 'Payé' : 'Crédité',
            self::STATUS_CANCELLED => 'Annulé',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
            self::STATUS_COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::STATUS_CANCELLED => 'bg-slate-100 text-slate-600',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function payoutMethodLabel(): ?string
    {
        return $this->payout_method
            ? (self::PAYOUT_METHODS[$this->payout_method] ?? $this->payout_method)
            : null;
    }
}
