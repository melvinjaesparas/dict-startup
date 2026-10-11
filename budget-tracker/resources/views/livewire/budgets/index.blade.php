<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-slate-600">Set a weekly allowance or monthly caps per category. Spending counts automatically from transactions with the same category.</p>
            <p class="mt-1 text-xs text-slate-500">Planned per month: <span class="font-semibold">{{ money($monthlyPlanned) }}</span></p>
        </div>
        <button class="btn-primary" wire:click="openForm">+ New budget</button>
    </div>

    @if ($budgets->isEmpty())
        <x-empty title="No budgets yet" text="Start with a weekly allowance, then add food, transport, and subscriptions caps.">
            <button class="btn-primary" wire:click="openForm">+ New budget</button>
        </x-empty>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($budgets as $b)
            @php $spent = $b->spent(); $pct = $b->usedPercent(); $left = $b->remaining(); @endphp
            <div class="card card-pad {{ $b->is_active ? '' : 'opacity-60' }}" wire:key="budget-{{ $b->id }}">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-2xl">{{ $b->icon ?: '💰' }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2"><span class="truncate font-bold">{{ $b->name }}</span><span class="badge-slate">{{ $b->period }}</span>@if (! $b->is_active)<span class="badge-amber">paused</span>@endif</div>
                        <div class="text-xs text-slate-500">category: {{ str_replace('_', ' ', $b->category) }} · {{ $b->profile->name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $b->periodStart()->format('M j') }} – {{ $b->periodEnd()->format('M j') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold tabular-nums {{ $left < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ money($left) }}</div>
                        <div class="text-[11px] text-slate-500">{{ $left < 0 ? 'over budget' : 'left' }}</div>
                    </div>
                </div>
                <x-progress :percent="$pct" class="mt-4" height="h-2.5" />
                <div class="mt-2 flex justify-between text-xs text-slate-600"><span>Spent {{ money($spent) }}</span><span>of {{ money($b->amount) }} ({{ $pct }}%)</span></div>
                <div class="mt-4 flex gap-2">
                    <button class="btn-primary btn-sm" wire:click="openSpend({{ $b->id }})">+ Log spend</button>
                    <span class="flex-1"></span>
                    <button class="btn-ghost btn-sm" wire:click="openForm({{ $b->id }})">Edit</button>
                    <button class="btn-ghost btn-sm text-red-600" wire:click="delete({{ $b->id }})" wire:confirm="Delete budget '{{ $b->name }}'?">Delete</button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'Edit budget' : 'New budget'">
            <form wire:submit="save" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-[auto_1fr]">
                    <x-field label="Icon" for="icon"><input id="icon" wire:model="icon" maxlength="4" class="input w-20 text-center text-lg"></x-field>
                    <x-field label="Name" for="name"><input id="name" wire:model="name" class="input" placeholder="e.g. Weekly allowance, Food, Transport"></x-field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Tracks category" for="category" hint="Expenses with this category count here.">
                        <input id="category" list="budget-cats" wire:model="category" class="input">
                        <datalist id="budget-cats">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                    </x-field>
                    <x-field label="Period" for="period">
                        <select id="period" wire:model="period" class="input"><option value="weekly">Weekly (Mon–Sun)</option><option value="monthly">Monthly</option></select>
                    </x-field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Limit (₱)" for="amount"><input id="amount" type="number" step="0.01" min="0" wire:model="amount" class="input"></x-field>
                    <x-field label="Profile" for="profile_id">
                        <select id="profile_id" wire:model="profile_id" class="input">@foreach ($profiles as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                    </x-field>
                </div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active" class="rounded"> Active</label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </x-modal>
    @endif

    @if ($showSpend)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center" x-data x-on:keydown.escape.window="$wire.closeSpend()">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closeSpend()">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold">Log spending</h2>
                    <button type="button" wire:click="closeSpend" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
                </div>
                <form wire:submit="spend" class="space-y-4 px-5 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-field label="Amount (₱)" for="spend_amount"><input id="spend_amount" type="number" step="0.01" min="0" wire:model="spend_amount" class="input" autofocus></x-field>
                        <x-field label="Date" for="spend_date"><input id="spend_date" type="date" wire:model="spend_date" class="input"></x-field>
                    </div>
                    <x-field label="What for" for="spend_description"><input id="spend_description" wire:model="spend_description" class="input" placeholder="e.g. Lunch, jeep fare"></x-field>
                    <x-field label="Paid from" for="spend_account_id">
                        <select id="spend_account_id" wire:model="spend_account_id" class="input">
                            <option value="">— no account —</option>
                            @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }} ({{ money($a->balance) }})</option>@endforeach
                        </select>
                    </x-field>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeSpend">Cancel</button>
                        <button type="submit" class="btn-primary">Log</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
