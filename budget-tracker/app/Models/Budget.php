<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    public const PERIODS = ['weekly' => 'Weekly', 'monthly' => 'Monthly'];

    protected $fillable = ['profile_id', 'name', 'category', 'period', 'amount', 'icon', 'is_active'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function scopeForProfile(Builder $query, ?int $profileId): Builder
    {
        return $profileId ? $query->where('profile_id', $profileId) : $query;
    }

    public function periodStart(): Carbon
    {
        return $this->period === 'weekly' ? now()->startOfWeek() : now()->startOfMonth();
    }

    public function periodEnd(): Carbon
    {
        return $this->period === 'weekly' ? now()->endOfWeek() : now()->endOfMonth();
    }

    public function spent(): float
    {
        return (float) Transaction::query()
            ->where('profile_id', $this->profile_id)
            ->where('type', 'expense')
            ->where('category', $this->category)
            ->whereBetween('date', [$this->periodStart()->toDateString(), $this->periodEnd()->toDateString()])
            ->sum('amount');
    }

    public function remaining(): float
    {
        return (float) $this->amount - $this->spent();
    }

    public function usedPercent(): int
    {
        if ((float) $this->amount <= 0) {
            return 0;
        }

        return (int) min(100, round($this->spent() / (float) $this->amount * 100));
    }
}
