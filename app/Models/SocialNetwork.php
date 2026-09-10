<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialNetwork extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'handle',
        'public_name',
        'profile_url',
        'follower_count',
        'creator_tier',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platformLabel(): string
    {
        return match ($this->platform) {
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            default => ucfirst($this->platform),
        };
    }

    public function tierLabel(): string
    {
        return match ($this->creator_tier) {
            'top' => 'Top',
            'medium' => 'Medium',
            'basic' => 'Basique',
            default => 'Non classé',
        };
    }

    /** @return array{label: string, url: ?string, meta: ?string} */
    public function adminViewLink(): array
    {
        $meta = collect([
            $this->handle,
            $this->follower_count !== null ? number_format($this->follower_count).' abonnés' : null,
            $this->tierLabel() !== 'Non classé' ? $this->tierLabel() : null,
        ])->filter()->implode(' · ');

        return [
            'label' => $this->platformLabel(),
            'url' => $this->profile_url,
            'meta' => $meta ?: null,
        ];
    }

    /** @return array{id: int, label: string, handle: ?string, tier: ?string} */
    public function adminTierData(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->platformLabel(),
            'handle' => $this->handle,
            'tier' => $this->creator_tier,
        ];
    }
}
