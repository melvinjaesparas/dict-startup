<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    public const TYPES = [
        'income' => 'Income',
        'expense' => 'Expense',
        'transfer_in' => 'Transfer in',
        'transfer_out' => 'Transfer out',
    ];

    public const CATEGORIES = [
        'allowance', 'food', 'transport', 'bills', 'housing', 'utilities', 'internet', 'subscription',
        'loan', 'savings', 'salary', 'client_payment', 'freelance', 'transfer', 'shopping', 'health',
        'family', 'entertainment', 'education', 'business', 'other',
    ];

    protected $fillable = [
        'profile_id', 'account_id', 'type', 'category', 'amount', 'date', 'description',
        'reference_type', 'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
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

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForProfile(Builder $query, ?int $profileId): Builder
    {
        return $profileId ? $query->where('profile_id', $profileId) : $query;
    }

    public function isInflow(): bool
    {
        return in_array($this->type, ['income', 'transfer_in']);
    }

    public function signedAmount(): float
    {
        return $this->isInflow() ? (float) $this->amount : -(float) $this->amount;
    }

    /**
     * Record a transaction and apply it to the account balance in one step.
     * Credit-card accounts track what is owed, so an expense increases the balance.
     */
    public static function record(array $attributes): self
    {
        $transaction = static::create($attributes);

        if ($transaction->account) {
            $delta = $transaction->signedAmount();
            if ($transaction->account->isLiability()) {
                $delta = -$delta;
            }
            $transaction->account->adjust($delta);
        }

        return $transaction;
    }

    /** Reverse the balance effect and delete. */
    public function reverseAndDelete(): void
    {
        if ($this->account) {
            $delta = -$this->signedAmount();
            if ($this->account->isLiability()) {
                $delta = -$delta;
            }
            $this->account->adjust($delta);
        }

        $this->delete();
    }
}
