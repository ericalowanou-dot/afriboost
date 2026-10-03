<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Mission extends Model
{
    public const NETWORKS = ['facebook', 'tiktok', 'instagram', 'youtube'];

    public const NETWORK_LABELS = [
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
    ];

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
        'social_networks',
        'content_type',
        'min_duration_seconds',
        'reward_usd',
        'reward_usd_medium',
        'reward_usd_top',
        'network_budgets',
        'max_participants',
        'image_path',
        'content_example_path',
        'rating',
        'starts_at',
        'ends_at',
        'content_retention_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reward_usd' => 'decimal:2',
            'reward_usd_medium' => 'decimal:2',
            'reward_usd_top' => 'decimal:2',
            'rating' => 'decimal:1',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'social_networks' => 'array',
            'network_budgets' => 'array',
            'content_retention_days' => 'integer',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('ends_at')
                ->orWhereDate('ends_at', '>=', now()->toDateString());
        })->where(function (Builder $q) {
            $q->whereNull('starts_at')
                ->orWhereDate('starts_at', '<=', now()->toDateString());
        });
    }

    public function scopeNetwork(Builder $query, ?string $network): Builder
    {
        if ($network && $network !== 'all') {
            $query->where(function (Builder $q) use ($network) {
                $q->where('social_network', $network)
                    ->orWhereJsonContains('social_networks', $network);
            });
        }

        return $query;
    }

    /** @return list<string> */
    public function selectedNetworks(): array
    {
        $networks = $this->social_networks;

        if (is_array($networks) && $networks !== []) {
            return array_values($networks);
        }

        return $this->social_network ? [$this->social_network] : [];
    }

    public function networkLabel(?string $network = null): string
    {
        $network ??= $this->social_network;

        return self::NETWORK_LABELS[$network] ?? ucfirst((string) $network);
    }

    public function networkLabels(): string
    {
        return collect($this->selectedNetworks())
            ->map(fn (string $network) => $this->networkLabel($network))
            ->implode(', ');
    }

    public function logoUrl(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function contentExampleUrl(): ?string
    {
        return $this->content_example_path
            ? Storage::disk('public')->url($this->content_example_path)
            : null;
    }

    /** Prix de la mission selon le niveau (tier) du créateur. */
    public function rewardFor(?User $user, ?string $network = null): float
    {
        $network ??= $this->social_network;
        $tier = match ($user?->tierForPlatform($network)) {
            User::TIER_TOP => 'top',
            User::TIER_MEDIUM => 'medium',
            default => 'basic',
        };

        $budgets = $this->network_budgets;
        if (is_array($budgets) && isset($budgets[$network][$tier])) {
            return (float) $budgets[$network][$tier];
        }

        return (float) match ($tier) {
            'top' => $this->reward_usd_top ?? $this->reward_usd,
            'medium' => $this->reward_usd_medium ?? $this->reward_usd,
            default => $this->reward_usd,
        };
    }

    /** Plus petite et plus grande récompense proposées, tous réseaux et niveaux confondus. */
    public function rewardRange(): array
    {
        $amounts = collect($this->network_budgets ?: [])
            ->flatMap(fn ($tiers) => array_values((array) $tiers))
            ->push($this->reward_usd, $this->reward_usd_medium, $this->reward_usd_top)
            ->filter(fn ($amount) => $amount !== null)
            ->map(fn ($amount) => (float) $amount);

        return [$amounts->min() ?? 0.0, $amounts->max() ?? 0.0];
    }

    public function contentTypeLabel(): string
    {
        return match ($this->content_type) {
            'video' => 'Vidéo',
            'post' => 'Publication',
            'story' => 'Story',
            default => ucfirst((string) $this->content_type),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'published' => 'Publiée',
            'closed' => 'Clôturée',
            default => ucfirst((string) $this->status),
        };
    }

    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->copy()->endOfDay()->isPast();
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->copy()->startOfDay()->lte(now());
    }

    /** Nombre de places prises (toutes participations sauf refusées). */
    public function takenSlots(): int
    {
        return $this->participations()
            ->where('status', '!=', Participation::STATUS_REJECTED)
            ->count();
    }

    public function remainingSlots(): ?int
    {
        if (! $this->max_participants) {
            return null;
        }

        return max(0, $this->max_participants - $this->takenSlots());
    }

    public function isFull(): bool
    {
        return $this->remainingSlots() === 0;
    }

    /** Raison pour laquelle on ne peut plus rejoindre la mission, ou null si elle est ouverte. */
    public function closedReason(): ?string
    {
        return match (true) {
            $this->status !== 'published' => 'Cette mission n\'est plus disponible.',
            ! $this->hasStarted() => 'Cette mission démarre le '.$this->starts_at->format('d/m/Y').'.',
            $this->hasEnded() => 'Cette mission est terminée.',
            $this->isFull() => 'Toutes les places de cette mission sont prises.',
            default => null,
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

    public function supportsNetwork(string $network): bool
    {
        return in_array($network, $this->selectedNetworks(), true);
    }

    /** Paramètres pour route('missions.show|participate|submit', ...) */
    public function routeParams(?string $preferredNetwork = null): array
    {
        $network = $preferredNetwork && $this->supportsNetwork($preferredNetwork)
            ? $preferredNetwork
            : $this->social_network;

        return [
            'reseau' => $network,
            'missionSlug' => $this->urlSlug(),
        ];
    }

    public function userParticipationNetwork(?User $user): ?string
    {
        if (! $user) {
            return $this->social_network;
        }

        foreach ($this->selectedNetworks() as $network) {
            if ($user->hasConnectedNetwork($network)) {
                return $network;
            }
        }

        return null;
    }

    public static function findByUrlSlug(string $missionSlug): ?self
    {
        if (! preg_match('/-(\d+)$/', $missionSlug, $matches)) {
            return null;
        }

        return static::query()->find((int) $matches[1]);
    }
}
