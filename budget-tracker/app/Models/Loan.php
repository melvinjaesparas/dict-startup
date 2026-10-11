<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    public const TYPES = [
        'term' => 'Term loan (e.g. GLoan, Maya Loan)',
        'credit_line' => 'Credit line (e.g. GCredit, Maya Credit)',
        'installment' => 'Installment (e.g. GGives)',
        'bnpl' => 'Buy now pay later (e.g. Atome)',
        'credit_card' => 'Credit card (e.g. BPI)',
        'other' => 'Other',
    ];

    public const STATUSES = ['active' => 'Active', 'paid' => 'Fully paid', 'closed' => 'Closed'];

    protected $fillable = [
        'profile_id', 'institution_id', 'account_id', 'name', 'type', 'principal', 'outstanding_balance',
        'credit_limit', 'interest_rate', 'term_months', 'monthly_payment', 'due_day', 'next_due_date',
        'start_date', 'end_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'monthly_payment' => 'decimal:2',
            'next_due_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class)->latest('paid_at');
    }

    public function scopeForProfile(Builder $query, ?int $profileId): Builder
    {
        return $profileId ? $query->where('profile_id', $profileId) : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function paidAmount(): float
    {
        return max(0, (float) $this->principal - (float) $this->outstanding_balance);
    }

    public function progressPercent(): int
    {
        if ((float) $this->principal <= 0) {
            return $this->status === 'paid' ? 100 : 0;
        }

        return (int) min(100, round($this->paidAmount() / (float) $this->principal * 100));
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->next_due_date && $this->next_due_date->isPast() && ! $this->next_due_date->isToday();
    }

    public function daysUntilDue(): ?int
    {
        return $this->next_due_date ? (int) now()->startOfDay()->diffInDays($this->next_due_date, false) : null;
    }

    /** Record a repayment: lowers the outstanding balance and (optionally) debits an account. */
    public function recordPayment(float $amount, ?int $accountId, Carbon $paidAt, ?string $note = null): LoanPayment
    {
        return DB::transaction(function () use ($amount, $accountId, $paidAt, $note) {
            $transaction = null;

            if ($accountId) {
                $transaction = Transaction::record([
                    'profile_id' => $this->profile_id,
                    'account_id' => $accountId,
                    'type' => 'expense',
                    'category' => 'loan',
                    'amount' => $amount,
                    'date' => $paidAt,
                    'description' => $this->institution?->name.' '.$this->name.' payment',
                ]);
            }

            $payment = $this->payments()->create([
                'account_id' => $accountId,
                'transaction_id' => $transaction?->id,
                'amount' => $amount,
                'paid_at' => $paidAt,
                'note' => $note,
            ]);

            if ($transaction) {
                $transaction->update(['reference_type' => LoanPayment::class, 'reference_id' => $payment->id]);
            }

            $this->outstanding_balance = max(0, round((float) $this->outstanding_balance - $amount, 2));

            if ($this->next_due_date) {
                $this->next_due_date = $this->next_due_date->copy()->addMonthNoOverflow();
                if ($this->due_day) {
                    $this->next_due_date = $this->next_due_date->setDay(min($this->due_day, $this->next_due_date->daysInMonth));
                }
            }

            if ((float) $this->outstanding_balance <= 0 && in_array($this->type, ['term', 'installment', 'bnpl'])) {
                $this->status = 'paid';
            }

            $this->save();

            return $payment;
        });
    }
}
