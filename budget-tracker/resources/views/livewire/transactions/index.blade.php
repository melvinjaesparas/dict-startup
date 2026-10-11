<div class="space-y-6">
    <div class="grid gap-4 lg:grid-cols-4">
        <div class="card card-pad flex items-center justify-between lg:col-span-1">
            <button class="btn-ghost btn-sm" wire:click="shiftMonth(-1)">‹</button>
            <div class="text-center">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Month</div>
                <div class="font-bold">{{ $monthLabel }}</div>
            </div>
            <button class="btn-ghost btn-sm" wire:click="shiftMonth(1)">›</button>
        </div>
        <x-stat label="Income" :value="money($income)" tone="green" />
        <x-stat label="Expenses" :value="money($expenses)" tone="red" />
        <x-stat label="Net" :value="money($income - $expenses)" :tone="$income - $expenses >= 0 ? 'green' : 'red'" />
    </div>

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="card lg:col-span-3">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-5 py-3">
                <select wire:model.live="typeFilter" class="input w-auto text-xs">
                    <option value="all">All types</option>
                    @foreach (\App\Models\Transaction::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
                <select wire:model.live="accountFilter" class="input w-auto text-xs">
                    <option value="">All accounts</option>
                    @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }}</option>@endforeach
                </select>
                <select wire:model.live="categoryFilter" class="input w-auto text-xs">
                    <option value="">All categories</option>
                    @foreach ($categories as $c)<option value="{{ $c }}">{{ ucfirst(str_replace('_', ' ', $c)) }}</option>@endforeach
                </select>
                <input wire:model.live.debounce.300ms="search" class="input w-40 text-xs" placeholder="Search…">
                <span class="flex-1"></span>
                <button class="btn-secondary" wire:click="openForm(null, 'income')">+ Income</button>
                <button class="btn-primary" wire:click="openForm(null, 'expense')">+ Expense</button>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Date</th><th>Description</th><th>Account</th><th>Category</th><th class="text-right">Amount</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($transactions as $t)
                            <tr wire:key="tx-{{ $t->id }}">
                                <td class="whitespace-nowrap text-slate-600">{{ $t->date->format('M j') }}</td>
                                <td>
                                    <div class="font-medium">{{ $t->description ?: ucfirst(str_replace('_', ' ', $t->category)) }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $t->profile->name }}</div>
                                </td>
                                <td>
                                    @if ($t->account)
                                        <div class="flex items-center gap-2"><x-logo :institution="$t->account->institution" size="h-6 w-6" rounded="rounded-md" /><span class="text-xs">{{ $t->account->name }}</span></div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td><span class="badge-slate">{{ str_replace('_', ' ', $t->category) }}</span> @if (str_starts_with($t->type, 'transfer'))<span class="badge-blue">{{ $t->type === 'transfer_in' ? 'in' : 'out' }}</span>@endif</td>
                                <td class="text-right font-semibold tabular-nums {{ $t->isInflow() ? 'text-emerald-600' : 'text-slate-900' }}">{{ $t->isInflow() ? '+' : '-' }}{{ money($t->amount) }}</td>
                                <td class="whitespace-nowrap text-right">
                                    @if (! $t->reference_type)
                                        <button class="btn-ghost btn-sm" wire:click="openForm({{ $t->id }})">Edit</button>
                                    @endif
                                    <button class="btn-ghost btn-sm text-red-600" wire:click="delete({{ $t->id }})" wire:confirm="Remove this transaction and restore the account balance?">✕</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-10 text-center text-sm text-slate-500">No transactions for this month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $transactions->links() }}</div>
            @endif
        </div>

        <div class="card">
            <div class="border-b border-slate-100 px-5 py-3 font-bold">Spending by category</div>
            <ul class="divide-y divide-slate-100">
                @forelse ($byCategory as $row)
                    <li class="px-5 py-2.5" wire:key="cat-{{ $row->category }}">
                        <div class="flex justify-between text-sm"><span>{{ ucfirst(str_replace('_', ' ', $row->category)) }}</span><span class="font-semibold tabular-nums">{{ money($row->total) }}</span></div>
                        <x-progress :percent="$expenses > 0 ? (int) round($row->total / $expenses * 100) : 0" color="#475569" class="mt-1" height="h-1.5" />
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-xs text-slate-500">No spending yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'Edit transaction' : ($type === 'income' ? 'Add income' : 'Add expense')">
            <form wire:submit="save" class="space-y-4">
                <div class="flex rounded-xl bg-slate-100 p-1 text-sm font-semibold">
                    <button type="button" class="flex-1 rounded-lg py-1.5 {{ $type === 'expense' ? 'bg-white shadow-sm text-red-600' : 'text-slate-500' }}" wire:click="$set('type', 'expense')">Expense</button>
                    <button type="button" class="flex-1 rounded-lg py-1.5 {{ $type === 'income' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500' }}" wire:click="$set('type', 'income')">Income</button>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Amount (₱)" for="amount"><input id="amount" type="number" step="0.01" min="0" wire:model="amount" class="input" autofocus></x-field>
                    <x-field label="Date" for="date"><input id="date" type="date" wire:model="date" class="input"></x-field>
                </div>
                <x-field label="Description" for="description"><input id="description" wire:model="description" class="input" placeholder="e.g. Lunch, Grab, Client payment"></x-field>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Category" for="category" hint="Match a budget's category (e.g. allowance, food) to count against it.">
                        <input id="category" list="category-options" wire:model="category" class="input">
                        <datalist id="category-options">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                    </x-field>
                    <x-field label="Profile" for="profile_id">
                        <select id="profile_id" wire:model="profile_id" class="input">@foreach ($profiles as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                    </x-field>
                </div>
                <x-field label="Account" for="account_id" hint="Balance is updated automatically.">
                    <select id="account_id" wire:model="account_id" class="input">
                        <option value="">— no account —</option>
                        @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }} ({{ money($a->balance) }})</option>@endforeach
                    </select>
                </x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
