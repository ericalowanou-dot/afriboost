<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const VERIFICATION_PENDING = 'pending';

    public const VERIFICATION_VERIFIED = 'verified';

    public const VERIFICATION_REJECTED = 'rejected';

    public const TIER_TOP = 'top';

    public const TIER_MEDIUM = 'medium';

    public const TIER_BASIC = 'basic';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'status_reason',
        'avatar_path',
        'verification_status',
        'creator_tier',
        'registration_step',
        'verified_at',
        'verified_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'registration_step' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCreator(): bool
    {
        return $this->role === 'creator';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasCompletedRegistration(): bool
    {
        return $this->registration_step >= 2;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED;
    }

    public function verificationStatusLabel(): string
    {
        return match ($this->verification_status) {
            self::VERIFICATION_VERIFIED => 'Vérifié',
            self::VERIFICATION_REJECTED => 'Refusé',
            default => 'En attente',
        };
    }

    public function creatorTierLabel(): string
    {
        return match ($this->creator_tier) {
            self::TIER_TOP => 'Top',
            self::TIER_MEDIUM => 'Medium',
            self::TIER_BASIC => 'Basique',
            default => 'Non classé',
        };
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(self::class, 'verified_by');
    }

    public function socialNetworks(): HasMany
    {
        return $this->hasMany(SocialNetwork::class);
    }

        /**
     * Indique si l'utilisateur a connecté et actif le réseau social donné
     * (ex: 'tiktok', 'instagram'...). Utilisé pour restreindre l'accès
     * aux missions ciblant une plateforme précise.
     */
        public function hasConnectedNetwork(string $platform): bool
        {
            return $this->socialNetworks()
                ->where('platform', $platform)
                ->where('status', 'active')
                ->exists();
        }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
