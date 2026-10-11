<?php

namespace App\Livewire\Budgets;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Profile;
use App\Models\Transaction;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Budget & Allowance')]
class Index extends Component
{
    use HasFormModal;

    public ?int $profile_id = null;

    public string $name = '';

    public string $category = '';

    public string $period = 'monthly';

    public string $amount = '';

    public string $icon = '';

    public bool $is_active = true;

    // quick spend
    public bool $showSpend = false;

    public ?int $spendBudgetId = null;

    public string $spend_amount = '';

    public ?int $spend_account_id = null;

    public string $spend_date = '';

    public string $spend_description = '';

    protected function rules(): array
    {
        return [
            'profile_id' => ['required', 'exists:profiles,id'],
            'name' => ['required', 'string', 'max:60'],
            'category' => ['required', 'string', 'max:40'],
            'period' => ['required', 'in:weekly,monthly'],
            'amount' => ['required', 'numeric', 'min:1'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $b = Budget::findOrFail($id);
            $this->fill(['profile_id' => $b->profile_id, 'name' => $b->name, 'category' => $b->category, 'period' => $b->period, 'amount' => (string) $b->amount, 'icon' => (string) $b->icon, 'is_active' => $b->is_active]);
        } else {
            $this->fill(['profile_id' => $this->defaultProfileId(), 'name' => '', 'category' => 'allowance', 'period' => 'weekly', 'amount' => '', 'icon' => '💸', 'is_active' => true]);
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['category'] = str_replace(' ', '_', strtolower(trim($data['category'])));
        $data['icon'] = $data['icon'] ?: null;

        if ($this->editingId) {
            Budget::findOrFail($this->editingId)->update($data);
        } else {
            Budget::create($data);
        }

        $this->toast('Budget saved');
        $this->closeForm();
    }

    public function delete(int $id): void
    {
        Budget::findOrFail($id)->delete();
        $this->toast('Budget deleted');
    }

    public function openSpend(int $id): void
    {
        $this->resetValidation();
        $this->spendBudgetId = $id;
        $this->spend_amount = '';
        $this->spend_account_id = null;
        $this->spend_date = now()->toDateString();
        $this->spend_description = '';
        $this->showSpend = true;
    }

    public function closeSpend(): void
    {
        $this->showSpend = false;
        $this->spendBudgetId = null;
    }

    public function spend(): void
    {
        $this->validate([
            'spend_amount' => ['required', 'numeric', 'min:0.01'],
            'spend_account_id' => ['nullable', 'exists:accounts,id'],
            'spend_date' => ['required', 'date'],
            'spend_description' => ['nullable', 'string', 'max:200'],
        ]);

        $budget = Budget::findOrFail($this->spendBudgetId);
        Transaction::record([
            'profile_id' => $budget->profile_id,
            'account_id' => $this->spend_account_id ?: null,
            'type' => 'expense',
            'category' => $budget->category,
            'amount' => (float) $this->spend_amount,
            'date' => $this->spend_date,
            'description' => $this->spend_description ?: $budget->name,
        ]);

        $this->toast('Logged '.money($this->spend_amount).' to '.$budget->name);
        $this->closeSpend();
    }

    public function render()
    {
        $pid = current_profile_id();
        $budgets = Budget::with('profile')->forProfile($pid)->orderByDesc('is_active')->orderBy('period')->orderBy('name')->get();

        return view('livewire.budgets.index', [
            'budgets' => $budgets,
            'monthlyPlanned' => $budgets->where('is_active', true)->sum(fn ($b) => $b->period === 'weekly' ? $b->amount * 52 / 12 : $b->amount),
            'accounts' => Account::with('institution')->where('is_active', true)->orderBy('name')->get(),
            'profiles' => Profile::orderBy('id')->get(),
            'categories' => collect(Transaction::CATEGORIES)->sort()->values(),
        ]);
    }
}
