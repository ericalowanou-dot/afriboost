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
}
