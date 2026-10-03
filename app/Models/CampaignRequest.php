<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRequest extends Model
{
    public const STATUSES = [
        'new' => 'Nouvelle',
        'contacted' => 'Contactée',
        'converted' => 'Convertie',
        'declined' => 'Déclinée',
    ];

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'objective',
        'networks',
        'budget_usd',
        'desired_start',
        'status',
        'campaign_id',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'networks' => 'array',
            'budget_usd' => 'decimal:2',
            'desired_start' => 'date',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'new' => 'bg-sky-100 text-sky-800',
            'contacted' => 'bg-amber-100 text-amber-800',
            'converted' => 'bg-emerald-100 text-emerald-800',
            default => 'bg-slate-100 text-slate-600',
        };
    }
}
