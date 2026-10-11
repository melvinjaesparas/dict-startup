<li class="px-5 py-3 {{ $b->is_active ? '' : 'opacity-60' }}" wire:key="bill-{{ $b->id }}">
    <div class="flex items-center gap-3">
        @if ($b->institution)
            <x-logo :institution="$b->institution" />
        @else
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-lg">{{ $b->icon ?: '🧾' }}</div>
        @endif
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="truncate text-sm font-semibold">{{ $b->name }}</span>
                @if ($b->category === 'subscription')
                    <span class="badge-violet">{{ \App\Models\Bill::SUBSCRIPTION_TYPES[$b->subcategory] ?? 'Other' }}</span>
                @else
                    <span class="badge-slate">{{ Str::before($b->categoryLabel(), ' (') }}</span>
                @endif
                @if ($b->auto_pay)<span class="badge-slate">auto-pay</span>@endif
                @if (! $b->is_active)<span class="badge-amber">paused</span>@endif
            </div>
            <div class="text-xs text-slate-500">
                {{ $b->vendor ?: '—' }} · {{ $b->frequencyLabel() }} · due {{ $b->next_due_date->format('M j, Y') }}
                @if ($b->account) · from {{ Str::startsWith($b->account->name, $b->account->institution->name) ? $b->account->name : $b->account->institution->name.' '.$b->account->name }} @endif
                · {{ $b->profile->name }}
            </div>
        </div>
        <div class="text-right">
            <div class="text-sm font-bold tabular-nums">{{ money($b->amountInPhp()) }}</div>
            @if ($b->currency !== 'PHP')<div class="text-[11px] text-slate-500">{{ money($b->amount, $b->currency) }}</div>@endif
        </div>
        @if ($b->is_active)<x-due-badge :days="$b->daysUntilDue()" />@endif
        <div class="flex items-center gap-1">
            @if ($b->is_active)
                <button class="btn-primary btn-sm" wire:click="openPayment({{ $b->id }})">Pay</button>
            @endif
            <div x-data="{ open: false }" class="relative">
                <button class="btn-ghost btn-sm" @click="open = !open">⋯</button>
                <div x-show="open" @click.outside="open = false" x-transition style="display:none" class="absolute right-0 z-10 mt-1 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-lg">
                    <button class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" wire:click="openForm({{ $b->id }})" @click="open = false">Edit</button>
                    <button class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" wire:click="toggleHistory({{ $b->id }})" @click="open = false">Payment history ({{ $b->payments->count() }})</button>
                    @if ($b->is_active && $b->frequency !== 'once')
                        <button class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" wire:click="skip({{ $b->id }})" @click="open = false">Skip this period</button>
                    @endif
                    <button class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" wire:click="toggleActive({{ $b->id }})" @click="open = false">{{ $b->is_active ? 'Pause / cancel' : 'Reactivate' }}</button>
                    <button class="block w-full px-3 py-1.5 text-left text-red-600 hover:bg-red-50" wire:click="delete({{ $b->id }})" wire:confirm="Delete '{{ $b->name }}' and its payment history?" @click="open = false">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @if ($historyId === $b->id)
        <div class="mt-3 rounded-xl bg-slate-50 px-4 py-2">
            @forelse ($b->payments as $p)
                <div class="flex items-center justify-between py-1.5 text-sm" wire:key="bp-{{ $p->id }}">
                    <div><span class="font-medium">{{ $p->paid_at->format('M j, Y') }}</span> <span class="text-xs text-slate-500">for {{ $p->period_date->format('M Y') }} · {{ $p->account?->name ?? 'no account' }}@if ($p->note) · {{ $p->note }}@endif</span></div>
                    <div class="font-semibold tabular-nums">{{ money($p->amount) }}</div>
                </div>
            @empty
                <div class="py-2 text-xs text-slate-500">No payments recorded yet.</div>
            @endforelse
        </div>
    @endif
</li>
