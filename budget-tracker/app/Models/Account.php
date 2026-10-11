<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public const TYPES = [
        'wallet' => 'E-wallet',
        'savings' => 'Savings',
        'checking' => 'Checking',
        'credit_card' => 'Credit card',
        'cash' => 'Cash on hand',
        'other' => 'Other',
    ];

    protected $fillable = [
        'profile_id', 'institution_id', 'name', 'type', 'balance', 'currency',
        'is_active', 'include_in_net_worth', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
            'include_in_net_worth' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeForProfile(Builder $query, ?int $profileId): Builder
    {
        return $profileId ? $query->where('profile_id', $profileId) : $query;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /** Credit cards are liabilities: a positive balance means money owed. */
    public function isLiability(): bool
    {
        return $this->type === 'credit_card';
    }

    public function adjust(float $delta): void
    {
        $this->balance = round((float) $this->balance + $delta, 2);
        $this->save();
    }
}
