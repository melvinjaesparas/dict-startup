<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Monthly bills" :value="money($monthlyTotal)" tone="amber" sub="house, utilities, family, insurance…" />
        <x-stat label="Due in next 7 days" :value="money($dueThisWeek)" tone="blue" />
        <x-stat label="Overdue" :value="$overdueCount" :tone="$overdueCount ? 'red' : 'green'" />
        <div class="card card-pad">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">By category / month</div>
            <ul class="mt-1 space-y-0.5 text-xs">
                @foreach ($byCategory->take(4) as $cat => $sum)
                    <li class="flex justify-between"><span>{{ Str::before(\App\Models\Bill::CATEGORIES[$cat] ?? $cat, ' (') }}</span><span class="font-semibold tabular-nums">{{ money($sum) }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-5 py-3">
            <div class="flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                @foreach (['all' => 'All', 'due' => 'Due ≤ 7d', 'overdue' => 'Overdue', 'inactive' => 'Paused'] as $k => $v)
                    <button class="rounded-lg px-3 py-1.5 {{ $filter === $k ? 'bg-white shadow-sm' : 'text-slate-500' }}" wire:click="$set('filter', '{{ $k }}')">{{ $v }}</button>
                @endforeach
            </div>
            <select wire:model.live="categoryFilter" class="input w-auto text-xs">
                <option value="all">All categories</option>
                @foreach (\App\Models\Bill::CATEGORIES as $k => $v)@if ($k !== 'subscription')<option value="{{ $k }}">{{ $v }}</option>@endif @endforeach
            </select>
            <input wire:model.live.debounce.300ms="search" class="input w-48 text-xs" placeholder="Search…">
            <span class="flex-1"></span>
            <button class="btn-primary" wire:click="openForm">+ Add bill</button>
        </div>
        @if ($bills->isEmpty())
            <div class="p-6"><x-empty title="No bills match" text="Add rent, Meralco, water, internet, insurance, and other recurring dues."><button class="btn-primary" wire:click="openForm">+ Add bill</button></x-empty></div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($bills as $b)
                    @include('livewire.bills.partials.row', ['b' => $b])
                @endforeach
            </ul>
        @endif
    </div>

    @if ($showForm) @include('livewire.bills.partials.form') @endif
    @if ($showPayment) @include('livewire.bills.partials.pay') @endif
</div>
