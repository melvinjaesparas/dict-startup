<?php

namespace App\Livewire\Savings;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Account;
use App\Models\Profile;
use App\Models\SavingsGoal;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Savings Goals')]
class Index extends Component
{
    use HasFormModal;

    public ?int $profile_id = null;

    public ?int $account_id = null;

    public string $name = '';

    public string $icon = '';

    public string $target_amount = '';

    public string $saved_amount = '0';

    public string $deadline = '';

    public string $notes = '';

    public bool $showDeposit = false;

    public ?int $depositId = null;

    public string $dep_amount = '';

    public ?int $dep_from_account_id = null;

    public string $dep_date = '';

    public string $dep_note = '';

    public bool $showCompleted = false;

    protected function rules(): array
    {
        return [
            'profile_id' => ['required', 'exists:profiles,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'name' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', 'max:10'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'saved_amount' => ['required', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $g = SavingsGoal::findOrFail($id);
            $this->fill(['profile_id' => $g->profile_id, 'account_id' => $g->account_id, 'name' => $g->name, 'icon' => (string) $g->icon, 'target_amount' => (string) $g->target_amount, 'saved_amount' => (string) $g->saved_amount, 'deadline' => $g->deadline?->toDateString() ?? '', 'notes' => (string) $g->notes]);
        } else {
            $this->fill(['profile_id' => $this->defaultProfileId(), 'account_id' => null, 'name' => '', 'icon' => '🎯', 'target_amount' => '', 'saved_amount' => '0', 'deadline' => '', 'notes' => '']);
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['account_id'] = $data['account_id'] ?: null;
        $data['deadline'] = $data['deadline'] ?: null;
        $data['icon'] = $data['icon'] ?: null;
        $data['notes'] = $data['notes'] ?: null;
        $data['is_completed'] = (float) $data['saved_amount'] >= (float) $data['target_amount'];

        if ($this->editingId) {
            SavingsGoal::findOrFail($this->editingId)->update($data);
        } else {
            SavingsGoal::create($data);
        }

        $this->toast('Goal saved');
        $this->closeForm();
    }

    public function delete(int $id): void
    {
        SavingsGoal::findOrFail($id)->delete();
        $this->toast('Goal deleted');
    }

    public function openDeposit(int $id): void
    {
        $this->resetValidation();
        $this->depositId = $id;
        $this->dep_amount = '';
        $this->dep_from_account_id = null;
        $this->dep_date = now()->toDateString();
        $this->dep_note = '';
        $this->showDeposit = true;
    }

    public function closeDeposit(): void
    {
        $this->showDeposit = false;
        $this->depositId = null;
    }

    public function deposit(): void
    {
        $this->validate([
            'dep_amount' => ['required', 'numeric', 'min:0.01'],
            'dep_from_account_id' => ['nullable', 'exists:accounts,id'],
            'dep_date' => ['required', 'date'],
            'dep_note' => ['nullable', 'string', 'max:200'],
        ]);

        $goal = SavingsGoal::findOrFail($this->depositId);
        $goal->deposit((float) $this->dep_amount, $this->dep_from_account_id, Carbon::parse($this->dep_date), $this->dep_note ?: null);
        $this->toast('Added '.money($this->dep_amount).' to '.$goal->name.($goal->is_completed ? ' 🎉 Goal reached!' : ''));
        $this->closeDeposit();
    }

    public function render()
    {
        $pid = current_profile_id();
        $goals = SavingsGoal::with(['account.institution', 'profile'])->forProfile($pid)
            ->when(! $this->showCompleted, fn ($q) => $q->where('is_completed', false))
            ->orderBy('is_completed')->orderByRaw('deadline is null')->orderBy('deadline')->get();

        $all = SavingsGoal::forProfile($pid)->get();

        return view('livewire.savings.index', [
            'goals' => $goals,
            'totalSaved' => $all->sum('saved_amount'),
            'totalTarget' => $all->where('is_completed', false)->sum('target_amount'),
            'totalRemaining' => $all->where('is_completed', false)->sum->remaining(),
            'accounts' => Account::with('institution')->where('is_active', true)->orderBy('name')->get(),
            'profiles' => Profile::orderBy('id')->get(),
        ]);
    }
}
