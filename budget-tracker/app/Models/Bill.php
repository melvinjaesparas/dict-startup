<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Bill extends Model
{
    public const CATEGORIES = [
        'housing' => 'Housing / Rent',
        'utilities' => 'Utilities (electric, water)',
        'internet' => 'Internet & mobile',
        'subscription' => 'Subscription',
        'insurance' => 'Insurance',
        'education' => 'Education',
        'family' => 'Family support',
        'business' => 'Business expense',
        'other' => 'Other',
    ];

    public const SUBSCRIPTION_TYPES = [
        'ai' => 'AI tools',
        'dev_tools' => 'Developer tools',
        'cloud' => 'Cloud & hosting',
        'productivity' => 'Productivity',
        'entertainment' => 'Entertainment',
        'other' => 'Other',
    ];

    public const FREQUENCIES = [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
        'once' => 'One-time',
    ];

    protected $fillable = [
        'profile_id', 'account_id', 'institution_id', 'name', 'vendor', 'category', 'subcategory', 'amount',
        'currency', 'frequency', 'next_due_date', 'auto_pay', 'is_active', 'icon', 'website', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'next_due_date' => 'date',
            'auto_pay' => 'boolean',
            'is_active' => 'boolean',
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

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class)->latest('paid_at');
    }

    public function scopeForProfile(Builder $query, ?int $profileId): Builder
    {
        return $profileId ? $query->where('profile_id', $profileId) : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSubscriptions(Builder $query): Builder
    {
        return $query->where('category', 'subscription');
    }

    public function scopeHousehold(Builder $query): Builder
    {
        return $query->where('category', '!=', 'subscription');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function frequencyLabel(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? ucfirst($this->frequency);
    }

    public function amountInPhp(): float
    {
        return to_php($this->amount, $this->currency);
    }

    /** Normalised cost per month in PHP, for totals. */
    public function monthlyCostPhp(): float
    {
        $php = $this->amountInPhp();

        return match ($this->frequency) {
            'weekly' => round($php * 52 / 12, 2),
            'quarterly' => round($php / 3, 2),
            'yearly' => round($php / 12, 2),
            'once' => 0,
            default => $php,
        };
    }

    public function isOverdue(): bool
    {
        return $this->is_active && $this->next_due_date->isPast() && ! $this->next_due_date->isToday();
    }

    public function daysUntilDue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->next_due_date, false);
    }

    public function advanceDueDate(): void
    {
        $date = $this->next_due_date->copy();
        $this->next_due_date = match ($this->frequency) {
            'weekly' => $date->addWeek(),
            'quarterly' => $date->addMonthsNoOverflow(3),
            'yearly' => $date->addYearNoOverflow(),
            'once' => $date,
            default => $date->addMonthNoOverflow(),
        };

        if ($this->frequency === 'once') {
            $this->is_active = false;
        }
    }

    /** Mark the current period as paid, debit the account, and roll the due date forward. */
    public function markPaid(float $amount, ?int $accountId, Carbon $paidAt, ?string $note = null): BillPayment
    {
        return DB::transaction(function () use ($amount, $accountId, $paidAt, $note) {
            $transaction = null;

            if ($accountId) {
                $transaction = Transaction::record([
                    'profile_id' => $this->profile_id,
                    'account_id' => $accountId,
                    'type' => 'expense',
                    'category' => $this->category === 'subscription' ? 'subscription' : ($this->category === 'other' ? 'bills' : $this->category),
                    'amount' => $amount,
                    'date' => $paidAt,
                    'description' => $this->name.($this->vendor ? ' ('.$this->vendor.')' : ''),
                ]);
            }

            $payment = $this->payments()->create([
                'account_id' => $accountId,
                'transaction_id' => $transaction?->id,
                'amount' => $amount,
                'period_date' => $this->next_due_date,
                'paid_at' => $paidAt,
                'note' => $note,
            ]);

            if ($transaction) {
                $transaction->update(['reference_type' => BillPayment::class, 'reference_id' => $payment->id]);
            }

            $this->advanceDueDate();
            $this->save();

            return $payment;
        });
    }
}
