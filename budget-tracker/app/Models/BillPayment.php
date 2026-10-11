<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillPayment extends Model
{
    protected $fillable = ['bill_id', 'account_id', 'transaction_id', 'amount', 'period_date', 'paid_at', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'period_date' => 'date', 'paid_at' => 'date'];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
