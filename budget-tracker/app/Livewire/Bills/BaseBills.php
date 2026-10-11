<?php

namespace App\Livewire\Bills;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Account;
use App\Models\Bill;
use App\Models\Institution;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/**
 * Shared behaviour for the Bills page (household dues) and the Subscriptions page.
 * Subscriptions are bills with category = subscription.
 */
abstract class BaseBills extends Component
{
    use HasFormModal;

    public string $filter = 'all'; // all | due | overdue | inactive

    public string $search = '';

    // form
    public ?int $profile_id = null;

    public ?int $account_id = null;

    public ?int $institution_id = null;

    public string $name = '';

    public string $vendor = '';

    public string $category = 'other';

    public string $subcategory = 'other';

    public string $amount = '';

    public string $currency = 'PHP';

    public string $frequency = 'monthly';

    public string $next_due_date = '';

    public bool $auto_pay = false;

    public bool $is_active = true;

    public string $icon = '';

    public string $website = '';

    public string $notes = '';

    // pay modal
    public bool $showPayment = false;

    public ?int $payingId = null;

    public string $pay_amount = '';

    public ?int $pay_account_id = null;

    public string $pay_date = '';

    public string $pay_note = '';

    public ?int $historyId = null;

    abstract protected function isSubscriptionPage(): bool;

    abstract protected function viewName(): string;

    protected function baseQuery(): Builder
    {
        $q = Bill::query()->forProfile(current_profile_id());

        return $this->isSubscriptionPage() ? $q->subscriptions() : $q->household();
    }

    protected function rules(): array
    {
        return [
            'profile_id' => ['required', 'exists:profiles,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'name' => ['required', 'string', 'max:80'],
            'vendor' => ['nullable', 'string', 'max:80'],
            'category' => ['required', 'in:'.implode(',', array_keys(Bill::CATEGORIES))],
            'subcategory' => ['nullable', 'in:'.implode(',', array_keys(Bill::SUBSCRIPTION_TYPES))],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:PHP,USD'],
            'frequency' => ['required', 'in:'.implode(',', array_keys(Bill::FREQUENCIES))],
            'next_due_date' => ['required', 'date'],
            'auto_pay' => ['boolean'],
            'is_active' => ['boolean'],
            'icon' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $b = Bill::findOrFail($id);
            $this->fill([
                'profile_id' => $b->profile_id, 'account_id' => $b->account_id, 'institution_id' => $b->institution_id,
                'name' => $b->name, 'vendor' => (string) $b->vendor, 'category' => $b->category,
                'subcategory' => $b->subcategory ?? 'other', 'amount' => (string) $b->amount, 'currency' => $b->currency,
                'frequency' => $b->frequency, 'next_due_date' => $b->next_due_date->toDateString(), 'auto_pay' => $b->auto_pay,
                'is_active' => $b->is_active, 'icon' => (string) $b->icon, 'website' => (string) $b->website, 'notes' => (string) $b->notes,
            ]);
        } else {
            $this->fill([
                'profile_id' => $this->defaultProfileId(), 'account_id' => null, 'institution_id' => null, 'name' => '', 'vendor' => '',
                'category' => $this->isSubscriptionPage() ? 'subscription' : 'utilities', 'subcategory' => 'ai',
                'amount' => '', 'currency' => $this->isSubscriptionPage() ? 'USD' : 'PHP', 'frequency' => 'monthly',
                'next_due_date' => now()->addMonth()->startOfMonth()->toDateString(), 'auto_pay' => $this->isSubscriptionPage(),
                'is_active' => true, 'icon' => $this->isSubscriptionPage() ? '🤖' : '🧾', 'website' => '', 'notes' => '',
            ]);
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->isSubscriptionPage()) {
            $data['category'] = 'subscription';
        } else {
            $data['subcategory'] = null;
        }

        foreach (['account_id', 'institution_id', 'vendor', 'icon', 'website', 'notes'] as $k) {
            $data[$k] = $data[$k] === '' ? null : $data[$k];
        }

        if ($this->editingId) {
            Bill::findOrFail($this->editingId)->update($data);
            $this->toast('Saved');
        } else {
            Bill::create($data);
            $this->toast('Added '.$data['name']);
        }

        $this->closeForm();
    }

    public function delete(int $id): void
    {
        Bill::findOrFail($id)->delete();
        $this->toast('Deleted');
    }

    public function toggleActive(int $id): void
    {
        $b = Bill::findOrFail($id);
        $b->update(['is_active' => ! $b->is_active]);
        $this->toast($b->is_active ? 'Reactivated' : 'Paused');
    }

    public function skip(int $id): void
    {
        $b = Bill::findOrFail($id);
        $b->advanceDueDate();
        $b->save();
        $this->toast('Skipped to '.$b->next_due_date->format('M j'));
    }

    public function openPayment(int $id): void
    {
        $this->resetValidation();
        $b = Bill::findOrFail($id);
        $this->payingId = $id;
        $this->pay_amount = (string) $b->amountInPhp();
        $this->pay_account_id = $b->account_id;
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
            'pay_amount' => ['required', 'numeric', 'min:0'],
            'pay_account_id' => ['nullable', 'exists:accounts,id'],
            'pay_date' => ['required', 'date'],
            'pay_note' => ['nullable', 'string', 'max:200'],
        ]);

        $bill = Bill::findOrFail($this->payingId);
        $bill->markPaid((float) $this->pay_amount, $this->pay_account_id, Carbon::parse($this->pay_date), $this->pay_note ?: null);
        $this->toast($bill->name.' paid · next due '.$bill->next_due_date->format('M j'));
        $this->closePayment();
    }

    public function toggleHistory(int $id): void
    {
        $this->historyId = $this->historyId === $id ? null : $id;
    }

    protected function filteredBills()
    {
        return $this->baseQuery()->with(['account.institution', 'institution', 'profile', 'payments.account'])
            ->when($this->filter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->filter !== 'inactive', fn ($q) => $q->where('is_active', true))
            ->when($this->filter === 'due', fn ($q) => $q->where('next_due_date', '<=', now()->addDays(7)->toDateString()))
            ->when($this->filter === 'overdue', fn ($q) => $q->where('next_due_date', '<', now()->toDateString()))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$this->search}%")->orWhere('vendor', 'like', "%{$this->search}%")))
            ->orderBy('next_due_date')->get();
    }

    protected function sharedViewData(): array
    {
        $active = $this->baseQuery()->active()->get();

        return [
            'bills' => $this->filteredBills(),
            'monthlyTotal' => $active->sum->monthlyCostPhp(),
            'yearlyTotal' => $active->sum->monthlyCostPhp() * 12,
            'dueThisWeek' => $active->filter(fn ($b) => $b->daysUntilDue() >= 0 && $b->daysUntilDue() <= 7)->sum->amountInPhp(),
            'overdueCount' => $active->filter->isOverdue()->count(),
            'accounts' => Account::with('institution')->where('is_active', true)->orderBy('name')->get(),
            'institutions' => Institution::orderBy('sort_order')->orderBy('name')->get(),
            'profiles' => Profile::orderBy('id')->get(),
        ];
    }
}
