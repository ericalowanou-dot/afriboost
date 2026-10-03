<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    public const STATUSES = [
        'draft' => 'Brouillon',
        'active' => 'Active',
        'completed' => 'Terminée',
        'archived' => 'Archivée',
    ];

    protected $fillable = [
        'client_name',
        'title',
        'objective',
        'budget_usd',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'budget_usd' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-100 text-emerald-800',
            'completed' => 'bg-sky-100 text-sky-800',
            'archived' => 'bg-slate-100 text-slate-600',
            default => 'bg-amber-100 text-amber-800',
        };
    }
}
