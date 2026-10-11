<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="grid grid-cols-2 gap-3">
            <x-stat label="Cash & savings" :value="money($assets)" tone="green" class="!p-4" />
            <x-stat label="Credit card balance" :value="money($cardDebt)" tone="red" class="!p-4" />
        </div>
        <div class="flex items-center gap-2">
            <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" wire:model.live="showInactive" class="rounded"> Show inactive</label>
            <button class="btn-secondary" wire:click="openTransfer">⇄ Transfer</button>
            <button class="btn-primary" wire:click="openForm">+ Add account</button>
        </div>
    </div>

    @if ($groups->isEmpty())
        <x-empty title="No accounts yet" text="Add your GCash, Maya, Maribank, UnionDigital, BPI and other accounts to start tracking balances.">
            <button class="btn-primary" wire:click="openForm">+ Add account</button>
        </x-empty>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($groups as $accounts)
            @php $inst = $accounts->first()->institution; @endphp
            <div class="card overflow-hidden" wire:key="inst-{{ $inst->id }}">
                <div class="flex items-center gap-3 px-5 py-4" style="background: linear-gradient(135deg, {{ $inst->color }}22, transparent)">
                    <x-logo :institution="$inst" size="h-11 w-11" />
                    <div class="min-w-0 flex-1">
                        <div class="font-bold">{{ $inst->name }}</div>
                        <div class="text-xs text-slate-500">{{ $inst->typeLabel() }} · {{ $accounts->count() }} {{ Str::plural('account', $accounts->count()) }}</div>
                    </div>
                    <div class="text-right text-sm font-bold tabular-nums">{{ money($accounts->reject->isLiability()->sum('balance') - $accounts->filter->isLiability()->sum('balance')) }}</div>
                </div>
                <ul class="divide-y divide-slate-100">
                    @foreach ($accounts as $a)
                        <li class="flex items-center gap-3 px-5 py-3 {{ $a->is_active ? '' : 'opacity-50' }}" wire:key="acct-{{ $a->id }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 text-sm font-semibold">
                                    <span class="truncate">{{ $a->name }}</span>
                                    @if (! $a->is_active)<span class="badge-slate">inactive</span>@endif
                                    @if (! $a->include_in_net_worth)<span class="badge-slate" title="Excluded from net worth">excl.</span>@endif
                                </div>
                                <div class="text-[11px] text-slate-500">{{ $a->typeLabel() }} · {{ $a->profile->name }}</div>
                            </div>
                            <div class="text-sm font-bold tabular-nums {{ $a->isLiability() ? 'text-red-600' : 'text-slate-900' }}">{{ $a->isLiability() ? '-' : '' }}{{ money($a->balance) }}</div>
                            <div class="flex gap-1">
                                <button class="btn-ghost btn-sm" title="Transfer from" wire:click="openTransfer({{ $a->id }})">⇄</button>
                                <button class="btn-ghost btn-sm" wire:click="openForm({{ $a->id }})">Edit</button>
                                <button class="btn-ghost btn-sm text-red-600" wire:click="delete({{ $a->id }})" wire:confirm="Delete '{{ $a->name }}'? Transactions are kept but unlinked.">✕</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'Edit account' : 'Add account'">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Profile" for="profile_id">
                        <select id="profile_id" wire:model="profile_id" class="input">
                            @foreach ($profiles as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="Bank / e-wallet" for="institution_id">
                        <select id="institution_id" wire:model="institution_id" class="input">
                            @foreach ($institutions as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach
                        </select>
                    </x-field>
                </div>
                <x-field label="Account name" for="name">
                    <input id="name" wire:model="name" class="input" placeholder="e.g. GCash Wallet, GSave, Maya Savings">
                </x-field>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Type" for="type">
                        <select id="type" wire:model="type" class="input">
                            @foreach (\App\Models\Account::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="Current balance (₱)" for="balance" hint="For credit cards, enter the amount you owe.">
                        <input id="balance" type="number" step="0.01" min="0" wire:model="balance" class="input">
                    </x-field>
                </div>
                <div class="flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="include_in_net_worth" class="rounded"> Include in net worth</label>
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_active" class="rounded"> Active</label>
                </div>
                <x-field label="Notes" for="notes">
                    <textarea id="notes" wire:model="notes" rows="2" class="input" placeholder="Account number hints, interest rate, etc."></textarea>
                </x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Save changes' : 'Add account' }}</button>
                </div>
            </form>
        </x-modal>
    @endif

    @if ($showTransfer)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center" x-data x-on:keydown.escape.window="$wire.closeTransfer()">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closeTransfer()">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold">Transfer between accounts</h2>
                    <button type="button" wire:click="closeTransfer" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
                </div>
                <form wire:submit="transfer" class="space-y-4 px-5 py-4">
                    <x-field label="From" for="from_account_id">
                        <select id="from_account_id" wire:model="from_account_id" class="input">
                            <option value="">Select…</option>
                            @foreach ($allAccounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }} ({{ money($a->balance) }})</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="To" for="to_account_id">
                        <select id="to_account_id" wire:model="to_account_id" class="input">
                            <option value="">Select…</option>
                            @foreach ($allAccounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }}</option>@endforeach
                        </select>
                    </x-field>
                    <div class="grid grid-cols-2 gap-4">
                        <x-field label="Amount (₱)" for="transfer_amount"><input id="transfer_amount" type="number" step="0.01" min="0" wire:model="transfer_amount" class="input"></x-field>
                        <x-field label="Date" for="transfer_date"><input id="transfer_date" type="date" wire:model="transfer_date" class="input"></x-field>
                    </div>
                    <x-field label="Note" for="transfer_note"><input id="transfer_note" wire:model="transfer_note" class="input" placeholder="e.g. Move to Maribank savings"></x-field>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeTransfer">Cancel</button>
                        <button type="submit" class="btn-primary">Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
