<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Total outstanding" :value="money($totalOwed)" tone="red" />
        <x-stat label="Monthly amortization" :value="money($monthlyDue)" tone="amber" />
        <x-stat label="Overdue" :value="$overdueCount" :tone="$overdueCount ? 'red' : 'green'" sub="active loans past due date" />
        <div class="card card-pad flex flex-col justify-between">
            <select wire:model.live="statusFilter" class="input">
                <option value="active">Active</option>
                <option value="paid">Fully paid</option>
                <option value="closed">Closed</option>
                <option value="all">All</option>
            </select>
            <button class="btn-primary mt-2 w-full" wire:click="openForm">+ Add loan / credit</button>
        </div>
    </div>

    @if ($loans->isEmpty())
        <x-empty title="No loans here" text="Track GLoan, GCredit, GGives, Maya Credit, Atome, credit cards, or any lender.">
            <button class="btn-primary" wire:click="openForm">+ Add loan</button>
        </x-empty>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($loans as $l)
            <div class="card" wire:key="loan-{{ $l->id }}">
                <div class="flex items-start gap-4 p-5">
                    <x-logo :institution="$l->institution" size="h-12 w-12" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold">{{ $l->name }}</span>
                            <span class="badge-slate">{{ Str::before($l->typeLabel(), ' (') }}</span>
                            @if ($l->status === 'paid')<span class="badge-green">Paid</span>@elseif ($l->status === 'closed')<span class="badge-slate">Closed</span>@else<x-due-badge :days="$l->daysUntilDue()" />@endif
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500">{{ $l->institution->name }} · {{ $l->profile->name }}@if ($l->interest_rate) · {{ $l->interest_rate }}% interest @endif @if ($l->term_months) · {{ $l->term_months }} mo @endif</div>

                        <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
                            <div><div class="text-[10px] font-semibold uppercase text-slate-500">Outstanding</div><div class="font-bold tabular-nums text-red-600">{{ money($l->outstanding_balance) }}</div></div>
                            <div><div class="text-[10px] font-semibold uppercase text-slate-500">Monthly</div><div class="font-bold tabular-nums">{{ money($l->monthly_payment) }}</div></div>
                            <div><div class="text-[10px] font-semibold uppercase text-slate-500">Next due</div><div class="font-semibold">{{ $l->next_due_date?->format('M j, Y') ?? '—' }}</div></div>
                        </div>

                        @if (in_array($l->type, ['term', 'installment', 'bnpl']) && (float) $l->principal > 0)
                            <div class="mt-3">
                                <div class="mb-1 flex justify-between text-[11px] text-slate-500"><span>Paid {{ money($l->paidAmount()) }} of {{ money($l->principal) }}</span><span>{{ $l->progressPercent() }}%</span></div>
                                <x-progress :percent="$l->progressPercent()" color="#7c3aed" />
                            </div>
                        @elseif ($l->credit_limit)
                            <div class="mt-3">
                                <div class="mb-1 flex justify-between text-[11px] text-slate-500"><span>Used {{ money($l->outstanding_balance) }} of {{ money($l->credit_limit) }} limit</span><span>{{ (int) min(100, round($l->outstanding_balance / max(1, $l->credit_limit) * 100)) }}%</span></div>
                                <x-progress :percent="(int) round($l->outstanding_balance / max(1, $l->credit_limit) * 100)" />
                            </div>
                        @endif
                        @if ($l->notes)<p class="mt-2 text-xs text-slate-500">{{ $l->notes }}</p>@endif
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 px-5 py-3">
                    @if ($l->status === 'active')
                        <button class="btn-primary btn-sm" wire:click="openPayment({{ $l->id }})">Record payment</button>
                    @endif
                    <button class="btn-secondary btn-sm" wire:click="toggleHistory({{ $l->id }})">History ({{ $l->payments->count() }})</button>
                    <span class="flex-1"></span>
                    <button class="btn-ghost btn-sm" wire:click="openForm({{ $l->id }})">Edit</button>
                    <button class="btn-ghost btn-sm text-red-600" wire:click="delete({{ $l->id }})" wire:confirm="Delete '{{ $l->name }}' and its payment history?">Delete</button>
                </div>
                @if ($historyId === $l->id)
                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                        @forelse ($l->payments as $p)
                            <div class="flex items-center justify-between py-1.5 text-sm" wire:key="lp-{{ $p->id }}">
                                <div><span class="font-medium">{{ $p->paid_at->format('M j, Y') }}</span> <span class="text-xs text-slate-500">{{ $p->account?->name ?? 'no account' }}@if ($p->note) · {{ $p->note }}@endif</span></div>
                                <div class="font-semibold tabular-nums">{{ money($p->amount) }}</div>
                            </div>
                        @empty
                            <div class="py-2 text-xs text-slate-500">No payments recorded yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'Edit loan / credit' : 'Add loan / credit'" maxWidth="max-w-2xl">
            <form wire:submit="save" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field label="Profile" for="profile_id">
                        <select id="profile_id" wire:model="profile_id" class="input">@foreach ($profiles as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                    </x-field>
                    <x-field label="Lender" for="institution_id">
                        <select id="institution_id" wire:model="institution_id" class="input">@foreach ($institutions as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select>
                    </x-field>
                    <x-field label="Type" for="type">
                        <select id="type" wire:model="type" class="input">@foreach (\App\Models\Loan::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                    </x-field>
                </div>
                <x-field label="Name" for="name"><input id="name" wire:model="name" class="input" placeholder="e.g. GLoan, GCredit, GGives - Laptop, Maya Credit, Atome - Phone"></x-field>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field label="Principal / amount borrowed (₱)" for="principal"><input id="principal" type="number" step="0.01" min="0" wire:model.blur="principal" class="input"></x-field>
                    <x-field label="Outstanding balance (₱)" for="outstanding_balance"><input id="outstanding_balance" type="number" step="0.01" min="0" wire:model="outstanding_balance" class="input"></x-field>
                    <x-field label="Credit limit (₱, optional)" for="credit_limit"><input id="credit_limit" type="number" step="0.01" min="0" wire:model="credit_limit" class="input"></x-field>
                </div>
                <div class="grid gap-4 sm:grid-cols-4">
                    <x-field label="Monthly payment (₱)" for="monthly_payment"><input id="monthly_payment" type="number" step="0.01" min="0" wire:model="monthly_payment" class="input"></x-field>
                    <x-field label="Interest %" for="interest_rate"><input id="interest_rate" type="number" step="0.01" min="0" wire:model="interest_rate" class="input"></x-field>
                    <x-field label="Term (months)" for="term_months"><input id="term_months" type="number" min="1" wire:model="term_months" class="input"></x-field>
                    <x-field label="Due day of month" for="due_day"><input id="due_day" type="number" min="1" max="31" wire:model="due_day" class="input"></x-field>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field label="Next due date" for="next_due_date"><input id="next_due_date" type="date" wire:model="next_due_date" class="input"></x-field>
                    <x-field label="Start date" for="start_date"><input id="start_date" type="date" wire:model="start_date" class="input"></x-field>
                    <x-field label="Status" for="status">
                        <select id="status" wire:model="status" class="input">@foreach (\App\Models\Loan::STATUSES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                    </x-field>
                </div>
                <x-field label="Usually paid from" for="account_id">
                    <select id="account_id" wire:model="account_id" class="input">
                        <option value="">— none —</option>
                        @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }}</option>@endforeach
                    </select>
                </x-field>
                <x-field label="Notes" for="notes"><textarea id="notes" wire:model="notes" rows="2" class="input" placeholder="Rate details, promo, reference numbers…"></textarea></x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn-primary">{{ $editingId ? 'Save changes' : 'Add loan' }}</button>
                </div>
            </form>
        </x-modal>
    @endif

    @if ($showPayment)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center" x-data x-on:keydown.escape.window="$wire.closePayment()">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closePayment()">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold">Record loan payment</h2>
                    <button type="button" wire:click="closePayment" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
                </div>
                <form wire:submit="recordPayment" class="space-y-4 px-5 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-field label="Amount (₱)" for="pay_amount"><input id="pay_amount" type="number" step="0.01" min="0" wire:model="pay_amount" class="input"></x-field>
                        <x-field label="Date paid" for="pay_date"><input id="pay_date" type="date" wire:model="pay_date" class="input"></x-field>
                    </div>
                    <x-field label="Paid from account" for="pay_account_id" hint="Deducts from this account's balance.">
                        <select id="pay_account_id" wire:model="pay_account_id" class="input">
                            <option value="">— don't touch any account —</option>
                            @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }} ({{ money($a->balance) }})</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="Note" for="pay_note"><input id="pay_note" wire:model="pay_note" class="input" placeholder="e.g. Month 5 of 12"></x-field>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closePayment">Cancel</button>
                        <button type="submit" class="btn-primary">Record payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
