<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Mission extends Model
{
    protected $fillable = [
        'campaign_id',
        'brand_name',
        'title',
        'short_description',
        'description',
        'objective',
        'instructions',
        'validation_criteria',
        'social_network',
        'content_type',
        'min_duration_seconds',
        'reward_usd',
        'max_participants',
        'image_path',
        'rating',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reward_usd' => 'decimal:2',
            'rating' => 'decimal:1',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeNetwork(Builder $query, ?string $network): Builder
    {
        if ($network && $network !== 'all') {
            $query->where('social_network', $network);
        }

        return $query;
    }

    public function networkLabel(): string
    {
        return match ($this->social_network) {
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            default => ucfirst($this->social_network),
        };
    }

    /** Slug lisible style CoinAfrique : titre-id */
    public function urlSlug(): string
    {
        $base = Str::slug($this->title);

        if ($base === '') {
            $base = Str::slug($this->brand_name) ?: 'mission';
        }

        return $base.'-'.$this->id;
    }

    /** Paramètres pour route('missions.show|participate|submit', ...) */
    public function routeParams(): array
    {
        return [
            'reseau' => $this->social_network,
            'missionSlug' => $this->urlSlug(),
        ];
    }

    public static function findByUrlSlug(string $missionSlug): ?self
    {
        if (! preg_match('/-(\d+)$/', $missionSlug, $matches)) {
            return null;
        }

        return static::query()->find((int) $matches[1]);
    }
}
