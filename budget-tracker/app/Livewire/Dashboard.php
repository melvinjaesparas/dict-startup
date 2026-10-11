<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Bill;
use App\Models\Budget;
use App\Models\Loan;
use App\Models\SavingsGoal;
use App\Models\Setting;
use App\Models\Transaction;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function payBill(int $billId): void
    {
        $bill = Bill::findOrFail($billId);
        $bill->markPaid((float) $bill->amountInPhp(), $bill->account_id, now(), 'Paid from dashboard');
        $this->dispatch('toast', message: $bill->name.' marked as paid');
    }

    public function payLoan(int $loanId): void
    {
        $loan = Loan::findOrFail($loanId);
        $amount = min((float) $loan->monthly_payment ?: (float) $loan->outstanding_balance, (float) $loan->outstanding_balance);
        $loan->recordPayment($amount, $loan->account_id, now(), 'Paid from dashboard');
        $this->dispatch('toast', message: $loan->name.' payment recorded');
    }

    public function render()
    {
        $pid = current_profile_id();
        $horizon = (int) Setting::get('reminder_days', 7);

        $accounts = Account::with('institution')->forProfile($pid)->where('is_active', true)->get();
        $assets = $accounts->where('include_in_net_worth', true)->reject->isLiability()->sum('balance');
        $cardDebt = $accounts->filter->isLiability()->sum('balance');

        $loans = Loan::with('institution')->forProfile($pid)->active()->orderBy('next_due_date')->get();
        $loanDebt = $loans->sum('outstanding_balance');

        $bills = Bill::with(['account.institution'])->forProfile($pid)->active()->orderBy('next_due_date')->get();
        $subscriptions = $bills->where('category', 'subscription');
        $household = $bills->where('category', '!=', 'subscription');

        $dues = collect();
        foreach ($bills as $bill) {
            $dues->push(['kind' => 'bill', 'id' => $bill->id, 'name' => $bill->name, 'sub' => $bill->vendor ?: $bill->categoryLabel(), 'icon' => $bill->icon, 'institution' => $bill->account?->institution, 'amount' => $bill->amountInPhp(), 'original' => $bill->currency !== 'PHP' ? money($bill->amount, $bill->currency) : null, 'date' => $bill->next_due_date, 'days' => $bill->daysUntilDue(), 'auto' => $bill->auto_pay]);
        }
        foreach ($loans->whereNotNull('next_due_date') as $loan) {
            $dues->push(['kind' => 'loan', 'id' => $loan->id, 'name' => $loan->name, 'sub' => $loan->institution->name, 'icon' => null, 'institution' => $loan->institution, 'amount' => min((float) $loan->monthly_payment ?: (float) $loan->outstanding_balance, (float) $loan->outstanding_balance), 'original' => null, 'date' => $loan->next_due_date, 'days' => $loan->daysUntilDue(), 'auto' => false]);
        }
        $dues = $dues->sortBy('date')->values();
        $overdue = $dues->where('days', '<', 0);
        $upcoming = $dues->where('days', '>=', 0)->where('days', '<=', $horizon);
        $later = $dues->where('days', '>', $horizon)->where('days', '<=', 30);

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $income = (float) Transaction::forProfile($pid)->where('type', 'income')->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');
        $expenses = (float) Transaction::forProfile($pid)->where('type', 'expense')->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');

        return view('livewire.dashboard', [
            'accounts' => $accounts,
            'assets' => $assets,
            'cardDebt' => $cardDebt,
            'loanDebt' => $loanDebt,
            'netWorth' => $assets - $cardDebt - $loanDebt,
            'loans' => $loans,
            'overdue' => $overdue,
            'upcoming' => $upcoming,
            'later' => $later,
            'horizon' => $horizon,
            'monthlyBills' => $household->sum->monthlyCostPhp(),
            'monthlySubs' => $subscriptions->sum->monthlyCostPhp(),
            'monthlyLoans' => $loans->sum('monthly_payment'),
            'income' => $income,
            'expenses' => $expenses,
            'goals' => SavingsGoal::forProfile($pid)->where('is_completed', false)->orderBy('deadline')->take(4)->get(),
            'budgets' => Budget::forProfile($pid)->where('is_active', true)->get(),
            'recent' => Transaction::with('account.institution')->forProfile($pid)->latest('date')->latest('id')->take(8)->get(),
            'usdRate' => Setting::usdRate(),
        ]);
    }
}
