<?php

namespace App\Livewire\Accounts;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Account;
use App\Models\Institution;
use App\Models\Profile;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Accounts & Wallets')]
class Index extends Component
{
    use HasFormModal;

    // form
    public ?int $profile_id = null;

    public ?int $institution_id = null;

    public string $name = '';

    public string $type = 'wallet';

    public string $balance = '0';

    public bool $include_in_net_worth = true;

    public bool $is_active = true;

    public string $notes = '';

    // transfer
    public bool $showTransfer = false;

    public ?int $from_account_id = null;

    public ?int $to_account_id = null;

    public string $transfer_amount = '';

    public string $transfer_date = '';

    public string $transfer_note = '';

    public bool $showInactive = false;

    protected function rules(): array
    {
        return [
            'profile_id' => ['required', 'exists:profiles,id'],
            'institution_id' => ['required', 'exists:institutions,id'],
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'in:'.implode(',', array_keys(Account::TYPES))],
            'balance' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'include_in_net_worth' => ['boolean'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $a = Account::findOrFail($id);
            $this->fill([
                'profile_id' => $a->profile_id, 'institution_id' => $a->institution_id, 'name' => $a->name,
                'type' => $a->type, 'balance' => (string) $a->balance, 'include_in_net_worth' => $a->include_in_net_worth,
                'is_active' => $a->is_active, 'notes' => (string) $a->notes,
            ]);
        } else {
            $this->fill([
                'profile_id' => $this->defaultProfileId(), 'institution_id' => Institution::orderBy('sort_order')->value('id'),
                'name' => '', 'type' => 'wallet', 'balance' => '0', 'include_in_net_worth' => true, 'is_active' => true, 'notes' => '',
            ]);
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            Account::findOrFail($this->editingId)->update($data);
            $this->toast('Account updated');
        } else {
            Account::create($data);
            $this->toast('Account added');
        }

        $this->closeForm();
    }

    public function delete(int $id): void
    {
        Account::findOrFail($id)->delete();
        $this->toast('Account deleted');
    }

    public function openTransfer(?int $fromId = null): void
    {
        $this->resetValidation();
        $this->from_account_id = $fromId;
        $this->to_account_id = null;
        $this->transfer_amount = '';
        $this->transfer_date = now()->toDateString();
        $this->transfer_note = '';
        $this->showTransfer = true;
    }

    public function closeTransfer(): void
    {
        $this->showTransfer = false;
    }

    public function transfer(): void
    {
        $this->validate([
            'from_account_id' => ['required', 'exists:accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:accounts,id'],
            'transfer_amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_date' => ['required', 'date'],
            'transfer_note' => ['nullable', 'string', 'max:200'],
        ], ['from_account_id.different' => 'Pick two different accounts.']);

        $from = Account::findOrFail($this->from_account_id);
        $to = Account::findOrFail($this->to_account_id);
        $amount = (float) $this->transfer_amount;
        $note = $this->transfer_note ?: "Transfer {$from->name} → {$to->name}";

        DB::transaction(function () use ($from, $to, $amount, $note) {
            Transaction::record(['profile_id' => $from->profile_id, 'account_id' => $from->id, 'type' => 'transfer_out', 'category' => 'transfer', 'amount' => $amount, 'date' => $this->transfer_date, 'description' => $note]);
            Transaction::record(['profile_id' => $to->profile_id, 'account_id' => $to->id, 'type' => 'transfer_in', 'category' => 'transfer', 'amount' => $amount, 'date' => $this->transfer_date, 'description' => $note]);
        });

        $this->toast('Transfer recorded');
        $this->showTransfer = false;
    }

    public function render()
    {
        $pid = current_profile_id();
        $accounts = Account::with(['institution', 'profile'])->forProfile($pid)
            ->when(! $this->showInactive, fn ($q) => $q->where('is_active', true))
            ->get()->sortBy(fn ($a) => [$a->institution->sort_order, $a->name]);

        return view('livewire.accounts.index', [
            'groups' => $accounts->groupBy('institution_id'),
            'assets' => $accounts->where('is_active', true)->reject->isLiability()->sum('balance'),
            'cardDebt' => $accounts->where('is_active', true)->filter->isLiability()->sum('balance'),
            'institutions' => Institution::orderBy('sort_order')->orderBy('name')->get(),
            'profiles' => Profile::orderBy('id')->get(),
            'allAccounts' => Account::with('institution')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
