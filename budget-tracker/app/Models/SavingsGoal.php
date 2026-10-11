<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SavingsGoal extends Model
{
    protected $fillable = [
        'profile_id', 'account_id', 'name', 'icon', 'target_amount', 'saved_amount', 'deadline', 'is_completed', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'saved_amount' => 'decimal:2',
            'deadline' => 'date',
            'is_completed' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeForProfile(Builder $query, ?int $profileId): Builder
    {
        return $profileId ? $query->where('profile_id', $profileId) : $query;
    }

    public function progressPercent(): int
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }

        return (int) min(100, round((float) $this->saved_amount / (float) $this->target_amount * 100));
    }

    public function remaining(): float
    {
        return max(0, (float) $this->target_amount - (float) $this->saved_amount);
    }

    /**
     * Add money to the goal. If a source account is given, the money is moved
     * from it into the goal's linked account (or just debited when no linked account).
     */
    public function deposit(float $amount, ?int $fromAccountId, Carbon $date, ?string $note = null): void
    {
        DB::transaction(function () use ($amount, $fromAccountId, $date, $note) {
            $description = 'Savings: '.$this->name.($note ? ' - '.$note : '');

            if ($fromAccountId && $fromAccountId !== $this->account_id) {
                Transaction::record([
                    'profile_id' => $this->profile_id,
                    'account_id' => $fromAccountId,
                    'type' => $this->account_id ? 'transfer_out' : 'expense',
                    'category' => 'savings',
                    'amount' => $amount,
                    'date' => $date,
                    'description' => $description,
                    'reference_type' => self::class,
                    'reference_id' => $this->id,
                ]);

                if ($this->account_id) {
                    Transaction::record([
                        'profile_id' => $this->profile_id,
                        'account_id' => $this->account_id,
                        'type' => 'transfer_in',
                        'category' => 'savings',
                        'amount' => $amount,
                        'date' => $date,
                        'description' => $description,
                        'reference_type' => self::class,
                        'reference_id' => $this->id,
                    ]);
                }
            }

            $this->saved_amount = round((float) $this->saved_amount + $amount, 2);
            $this->is_completed = (float) $this->saved_amount >= (float) $this->target_amount;
            $this->save();
        });
    }
}
