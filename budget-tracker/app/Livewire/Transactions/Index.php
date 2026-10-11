<?php

namespace App\Livewire\Transactions;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Account;
use App\Models\Profile;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Transactions')]
class Index extends Component
{
    use HasFormModal, WithPagination;

    #[Url]
    public string $month = '';

    #[Url]
    public string $typeFilter = 'all';

    #[Url]
    public string $accountFilter = '';

    #[Url]
    public string $categoryFilter = '';

    public string $search = '';

    // form
    public ?int $profile_id = null;

    public ?int $account_id = null;

    public string $type = 'expense';

    public string $category = 'allowance';

    public string $amount = '';

    public string $date = '';

    public string $description = '';

    public function mount(): void
    {
        if ($this->month === '') {
            $this->month = now()->format('Y-m');
        }
    }

    protected function rules(): array
    {
        return [
            'profile_id' => ['required', 'exists:profiles,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:40'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function updating($name): void
    {
        if (in_array($name, ['month', 'typeFilter', 'accountFilter', 'categoryFilter', 'search'])) {
            $this->resetPage();
        }
    }

    public function openForm(?int $id = null, string $type = 'expense', ?string $category = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $t = Transaction::findOrFail($id);
            $this->fill([
                'profile_id' => $t->profile_id, 'account_id' => $t->account_id, 'type' => $t->type, 'category' => $t->category,
                'amount' => (string) $t->amount, 'date' => $t->date->toDateString(), 'description' => (string) $t->description,
            ]);
        } else {
            $this->fill([
                'profile_id' => $this->defaultProfileId(), 'account_id' => null, 'type' => $type,
                'category' => $category ?? ($type === 'income' ? 'client_payment' : 'allowance'),
                'amount' => '', 'date' => now()->toDateString(), 'description' => '',
            ]);
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['account_id'] = $data['account_id'] ?: null;
        $data['description'] = $data['description'] ?: null;
        $data['category'] = str_replace(' ', '_', strtolower(trim($data['category'])));

        DB::transaction(function () use ($data) {
            if ($this->editingId) {
                $old = Transaction::findOrFail($this->editingId);
                if (in_array($old->type, ['transfer_in', 'transfer_out'])) {
                    $data['type'] = $old->type; // keep transfer legs as transfers
                }
                $old->reverseAndDelete();
            }
            Transaction::record($data);
        });

        $this->toast($this->editingId ? 'Transaction updated' : 'Transaction added');
        $this->closeForm();
    }

    public function delete(int $id): void
    {
        Transaction::findOrFail($id)->reverseAndDelete();
        $this->toast('Transaction removed and balance restored');
    }

    public function shiftMonth(int $delta): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)->addMonths($delta)->format('Y-m');
        $this->resetPage();
    }

    public function render()
    {
        $pid = current_profile_id();
        $start = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $base = Transaction::query()->forProfile($pid)->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        $filtered = (clone $base)
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->accountFilter !== '', fn ($q) => $q->where('account_id', $this->accountFilter))
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->search !== '', fn ($q) => $q->where('description', 'like', "%{$this->search}%"));

        $byCategory = (clone $base)->where('type', 'expense')->selectRaw('category, sum(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        return view('livewire.transactions.index', [
            'transactions' => $filtered->with('account.institution', 'profile')->latest('date')->latest('id')->paginate(25),
            'income' => (float) (clone $base)->where('type', 'income')->sum('amount'),
            'expenses' => (float) (clone $base)->where('type', 'expense')->sum('amount'),
            'byCategory' => $byCategory,
            'monthLabel' => $start->format('F Y'),
            'accounts' => Account::with('institution')->orderBy('name')->get(),
            'profiles' => Profile::orderBy('id')->get(),
            'categories' => collect(Transaction::CATEGORIES)->merge(Transaction::query()->distinct()->pluck('category'))->unique()->sort()->values(),
        ]);
    }
}
