<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participation extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'mission_id',
        'content_url',
        'screenshot_path',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_SUBMITTED => 'Soumise',
            self::STATUS_UNDER_REVIEW => 'En vérification',
            self::STATUS_VALIDATED => 'Validée',
            self::STATUS_REJECTED => 'Refusée',
            self::STATUS_PAID => 'Payée',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'bg-amber-100 text-amber-800',
            self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW => 'bg-orange-100 text-orange-800',
            self::STATUS_VALIDATED, self::STATUS_PAID => 'bg-emerald-100 text-emerald-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
