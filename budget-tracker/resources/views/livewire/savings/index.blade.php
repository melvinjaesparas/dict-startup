<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Total saved" :value="money($totalSaved)" tone="green" />
        <x-stat label="Target (open goals)" :value="money($totalTarget)" tone="blue" />
        <x-stat label="Still to save" :value="money($totalRemaining)" tone="amber" />
        <div class="card card-pad flex flex-col justify-between">
            <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" wire:model.live="showCompleted" class="rounded"> Show completed</label>
            <button class="btn-primary mt-2 w-full" wire:click="openForm">+ New goal</button>
        </div>
    </div>

    @if ($goals->isEmpty())
        <x-empty title="No savings goals" text="Set up an emergency fund, gadget fund, or travel fund and log deposits as you go.">
            <button class="btn-primary" wire:click="openForm">+ New goal</button>
        </x-empty>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($goals as $g)
            @php $pct = $g->progressPercent(); @endphp
            <div class="card card-pad" wire:key="goal-{{ $g->id }}">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-2xl">{{ $g->icon ?: '🎯' }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2"><span class="truncate font-bold">{{ $g->name }}</span>@if ($g->is_completed)<span class="badge-green">Done</span>@endif</div>
                        <div class="text-xs text-slate-500">
                            {{ $g->profile->name }}
                            @if ($g->account) · in {{ $g->account->institution->name }} {{ $g->account->name }} @endif
                            @if ($g->deadline) · by {{ $g->deadline->format('M j, Y') }} @endif
                        </div>
                    </div>
                    <div class="text-right text-lg font-bold text-emerald-600">{{ $pct }}%</div>
                </div>
                <x-progress :percent="$pct" color="#059669" class="mt-4" height="h-2.5" />
                <div class="mt-2 flex justify-between text-xs text-slate-600">
                    <span class="font-semibold">{{ money($g->saved_amount) }}</span>
                    <span>of {{ money($g->target_amount) }} · {{ money($g->remaining()) }} left</span>
                </div>
                @if ($g->deadline && ! $g->is_completed && $g->remaining() > 0)
                    @php $months = max(1, (int) ceil(now()->diffInMonths($g->deadline, false))); @endphp
                    <div class="mt-1 text-[11px] text-slate-500">≈ {{ money($g->remaining() / $months) }} / month to hit the deadline</div>
                @endif
                @if ($g->notes)<p class="mt-2 text-xs text-slate-500">{{ $g->notes }}</p>@endif
                <div class="mt-4 flex gap-2">
                    @if (! $g->is_completed)<button class="btn-primary btn-sm" wire:click="openDeposit({{ $g->id }})">+ Add savings</button>@endif
                    <span class="flex-1"></span>
                    <button class="btn-ghost btn-sm" wire:click="openForm({{ $g->id }})">Edit</button>
                    <button class="btn-ghost btn-sm text-red-600" wire:click="delete({{ $g->id }})" wire:confirm="Delete goal '{{ $g->name }}'?">Delete</button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'Edit goal' : 'New savings goal'">
            <form wire:submit="save" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-[auto_1fr]">
                    <x-field label="Icon" for="icon"><input id="icon" wire:model="icon" maxlength="4" class="input w-20 text-center text-lg"></x-field>
                    <x-field label="Goal name" for="name"><input id="name" wire:model="name" class="input" placeholder="e.g. Emergency fund, New MacBook"></x-field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Target (₱)" for="target_amount"><input id="target_amount" type="number" step="0.01" min="0" wire:model="target_amount" class="input"></x-field>
                    <x-field label="Already saved (₱)" for="saved_amount"><input id="saved_amount" type="number" step="0.01" min="0" wire:model="saved_amount" class="input"></x-field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Deadline" for="deadline"><input id="deadline" type="date" wire:model="deadline" class="input"></x-field>
                    <x-field label="Profile" for="profile_id">
                        <select id="profile_id" wire:model="profile_id" class="input">@foreach ($profiles as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                    </x-field>
                </div>
                <x-field label="Where the money sits" for="account_id" hint="Deposits from another account will be transferred here.">
                    <select id="account_id" wire:model="account_id" class="input">
                        <option value="">— not linked —</option>
                        @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }}</option>@endforeach
                    </select>
                </x-field>
                <x-field label="Notes" for="notes"><textarea id="notes" wire:model="notes" rows="2" class="input"></textarea></x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn-primary">Save goal</button>
                </div>
            </form>
        </x-modal>
    @endif

    @if ($showDeposit)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center" x-data x-on:keydown.escape.window="$wire.closeDeposit()">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closeDeposit()">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold">Add to savings</h2>
                    <button type="button" wire:click="closeDeposit" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
                </div>
                <form wire:submit="deposit" class="space-y-4 px-5 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-field label="Amount (₱)" for="dep_amount"><input id="dep_amount" type="number" step="0.01" min="0" wire:model="dep_amount" class="input" autofocus></x-field>
                        <x-field label="Date" for="dep_date"><input id="dep_date" type="date" wire:model="dep_date" class="input"></x-field>
                    </div>
                    <x-field label="Move money from" for="dep_from_account_id" hint="Optional. Records a transfer and updates balances.">
                        <select id="dep_from_account_id" wire:model="dep_from_account_id" class="input">
                            <option value="">— just update the goal —</option>
                            @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }} ({{ money($a->balance) }})</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="Note" for="dep_note"><input id="dep_note" wire:model="dep_note" class="input"></x-field>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeDeposit">Cancel</button>
                        <button type="submit" class="btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
