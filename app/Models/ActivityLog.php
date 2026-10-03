<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** Couleur du badge selon la famille d'action. */
    public function tone(): string
    {
        return match (true) {
            str_ends_with($this->action, 'validated'),
            str_ends_with($this->action, 'verified'),
            str_ends_with($this->action, 'completed') => 'bg-emerald-100 text-emerald-800',
            str_ends_with($this->action, 'rejected'),
            str_ends_with($this->action, 'cancelled'),
            str_ends_with($this->action, 'deleted') => 'bg-red-100 text-red-800',
            str_starts_with($this->action, 'payout') => 'bg-amber-100 text-amber-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
