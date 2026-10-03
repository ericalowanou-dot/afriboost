<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'network',
        'content_url',
        'screenshot_path',
        'reward_usd',
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
            'reward_usd' => 'decimal:2',
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

    /** Réseau réellement utilisé pour la mission (repli sur le réseau principal). */
    public function effectiveNetwork(): ?string
    {
        return $this->network ?: $this->mission?->social_network;
    }

    /** Montant figé à la validation, sinon montant estimé selon le classement actuel. */
    public function rewardAmount(): float
    {
        if ($this->reward_usd !== null) {
            return (float) $this->reward_usd;
        }

        return $this->mission?->rewardFor($this->user, $this->effectiveNetwork()) ?? 0.0;
    }

    public function screenshotUrl(): ?string
    {
        return $this->screenshot_path
            ? Storage::disk('public')->url($this->screenshot_path)
            : null;
    }

    public function isPendingReview(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW], true);
    }

    /** @return list<array{label: string, url: ?string, meta: ?string}> */
    public function adminViewLinks(): array
    {
        $links = [];

        if ($this->content_url) {
            $links[] = [
                'label' => 'Contenu soumis',
                'url' => $this->content_url,
                'meta' => $this->mission?->networkLabel($this->effectiveNetwork()).' · '.$this->mission?->brand_name,
            ];
        }

        $links[] = [
            'label' => 'Fiche créateur',
            'url' => route('admin.creators.show', $this->user_id),
            'meta' => $this->user?->name,
        ];

        foreach ($this->user?->socialNetworks ?? [] as $network) {
            if ($network->profile_url) {
                $links[] = $network->adminViewLink();
            }
        }

        return $links;
    }

    /** @return list<array{id: int, label: string, handle: ?string, tier: ?string}> */
    public function adminTierNetworks(): array
    {
        $networks = $this->user?->socialNetworks ?? collect();
        $missionNetwork = $this->effectiveNetwork();

        if ($missionNetwork) {
            $networks = $networks->sortByDesc(fn (SocialNetwork $network) => $network->platform === $missionNetwork);
        }

        return $networks
            ->map(fn (SocialNetwork $network) => $network->adminTierData())
            ->values()
            ->all();
    }
}
