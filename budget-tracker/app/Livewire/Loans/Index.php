<?php

namespace App\Livewire\Loans;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Account;
use App\Models\Institution;
use App\Models\Loan;
use App\Models\Profile;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Loans & Credit')]
class Index extends Component
{
    use HasFormModal;

    public string $statusFilter = 'active';

    // form
    public ?int $profile_id = null;

    public ?int $institution_id = null;

    public ?int $account_id = null;

    public string $name = '';

    public string $type = 'term';

    public string $principal = '';

    public string $outstanding_balance = '';

    public string $credit_limit = '';

    public string $interest_rate = '';

    public string $term_months = '';

    public string $monthly_payment = '';

    public string $due_day = '';

    public string $next_due_date = '';

    public string $start_date = '';

    public string $status = 'active';

    public string $notes = '';

    // payment modal
    public bool $showPayment = false;

    public ?int $payingId = null;

    public string $pay_amount = '';

    public ?int $pay_account_id = null;

    public string $pay_date = '';

    public string $pay_note = '';

    public ?int $historyId = null;

    protected function rules(): array
    {
        return [
            'profile_id' => ['required', 'exists:profiles,id'],
            'institution_id' => ['required', 'exists:institutions,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'in:'.implode(',', array_keys(Loan::TYPES))],
            'principal' => ['required', 'numeric', 'min:0'],
            'outstanding_balance' => ['required', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'term_months' => ['nullable', 'integer', 'min:1', 'max:600'],
            'monthly_payment' => ['required', 'numeric', 'min:0'],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'next_due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,paid,closed'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $l = Loan::findOrFail($id);
            $this->fill([
                'profile_id' => $l->profile_id, 'institution_id' => $l->institution_id, 'account_id' => $l->account_id,
                'name' => $l->name, 'type' => $l->type, 'principal' => (string) $l->principal,
                'outstanding_balance' => (string) $l->outstanding_balance, 'credit_limit' => (string) ($l->credit_limit ?? ''),
                'interest_rate' => (string) ($l->interest_rate ?? ''), 'term_months' => (string) ($l->term_months ?? ''),
                'monthly_payment' => (string) $l->monthly_payment, 'due_day' => (string) ($l->due_day ?? ''),
                'next_due_date' => $l->next_due_date?->toDateString() ?? '', 'start_date' => $l->start_date?->toDateString() ?? '',
                'status' => $l->status, 'notes' => (string) $l->notes,
            ]);
        } else {
            $this->fill([
                'profile_id' => $this->defaultProfileId(), 'institution_id' => Institution::where('slug', 'gcash')->value('id') ?? Institution::orderBy('sort_order')->value('id'),
                'account_id' => null, 'name' => '', 'type' => 'term', 'principal' => '', 'outstanding_balance' => '', 'credit_limit' => '',
                'interest_rate' => '', 'term_months' => '', 'monthly_payment' => '', 'due_day' => '',
                'next_due_date' => now()->addMonth()->toDateString(), 'start_date' => now()->toDateString(), 'status' => 'active', 'notes' => '',
            ]);
        }

        $this->showForm = true;
    }

    public function updatedPrincipal(): void
    {
        if (! $this->editingId && $this->outstanding_balance === '') {
            $this->outstanding_balance = $this->principal;
        }
    }

    public function save(): void
    {
        $data = $this->validate();
        foreach (['account_id', 'credit_limit', 'interest_rate', 'term_months', 'due_day', 'next_due_date', 'start_date'] as $k) {
            $data[$k] = $data[$k] === '' ? null : $data[$k];
        }

        if ($this->editingId) {
            Loan::findOrFail($this->editingId)->update($data);
            $this->toast('Loan updated');
        } else {
            Loan::create($data);
            $this->toast('Loan added');
        }

        $this->closeForm();
    }

    public function delete(int $id): void
    {
        Loan::findOrFail($id)->delete();
        $this->toast('Loan deleted');
    }

    public function openPayment(int $id): void
    {
        $this->resetValidation();
        $l = Loan::findOrFail($id);
        $this->payingId = $id;
        $this->pay_amount = (string) min((float) $l->monthly_payment ?: (float) $l->outstanding_balance, (float) $l->outstanding_balance);
        $this->pay_account_id = $l->account_id;
        $this->pay_date = now()->toDateString();
        $this->pay_note = '';
        $this->showPayment = true;
    }

    public function closePayment(): void
    {
        $this->showPayment = false;
        $this->payingId = null;
    }

    public function recordPayment(): void
    {
        $this->validate([
            'pay_amount' => ['required', 'numeric', 'min:0.01'],
            'pay_account_id' => ['nullable', 'exists:accounts,id'],
            'pay_date' => ['required', 'date'],
            'pay_note' => ['nullable', 'string', 'max:200'],
        ]);

        $loan = Loan::findOrFail($this->payingId);
        $loan->recordPayment((float) $this->pay_amount, $this->pay_account_id, Carbon::parse($this->pay_date), $this->pay_note ?: null);
        $this->toast('Payment of '.money($this->pay_amount).' recorded');
        $this->closePayment();
    }

    public function toggleHistory(int $id): void
    {
        $this->historyId = $this->historyId === $id ? null : $id;
    }

    public function render()
    {
        $pid = current_profile_id();
        $loans = Loan::with(['institution', 'account', 'profile', 'payments.account'])
            ->forProfile($pid)
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByRaw('next_due_date is null')->orderBy('next_due_date')->get();

        $active = Loan::forProfile($pid)->active()->get();

        return view('livewire.loans.index', [
            'loans' => $loans,
            'totalOwed' => $active->sum('outstanding_balance'),
            'monthlyDue' => $active->sum('monthly_payment'),
            'overdueCount' => $active->filter->isOverdue()->count(),
            'institutions' => Institution::orderBy('sort_order')->orderBy('name')->get(),
            'accounts' => Account::with('institution')->where('is_active', true)->orderBy('name')->get(),
            'profiles' => Profile::orderBy('id')->get(),
        ]);
    }
}
