<div class="space-y-6">
    {{-- Headline numbers --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Net worth" :value="money($netWorth)" :tone="$netWorth >= 0 ? 'green' : 'red'" sub="Assets − credit card − loans" />
        <x-stat label="Cash & savings" :value="money($assets)" tone="blue" :sub="$accounts->count().' active accounts'" />
        <x-stat label="Total owed" :value="money($loanDebt + $cardDebt)" tone="red" :sub="'Loans '.money($loanDebt).' · Cards '.money($cardDebt)" />
        <x-stat label="Monthly commitments" :value="money($monthlyBills + $monthlySubs + $monthlyLoans)" tone="amber" :sub="'Bills '.money($monthlyBills).' · Subs '.money($monthlySubs).' · Loans '.money($monthlyLoans)" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Dues --}}
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold">Dues & reminders</h2>
                    <p class="text-xs text-slate-500">Bills, subscriptions and loan amortizations due in the next {{ $horizon }} days</p>
                </div>
                <a href="{{ route('bills') }}" wire:navigate class="btn-secondary btn-sm">All bills</a>
            </div>

            @if ($overdue->isEmpty() && $upcoming->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-slate-500">🎉 Nothing due in the next {{ $horizon }} days.</div>
            @endif

            @foreach ([['Overdue', $overdue, 'text-red-600'], ['Due soon', $upcoming, 'text-slate-700'], ['Later this month', $later, 'text-slate-500']] as [$heading, $items, $cls])
                @if ($items->isNotEmpty())
                    <div class="px-5 pt-4 text-xs font-bold uppercase tracking-wide {{ $cls }}">{{ $heading }}</div>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($items as $d)
                            <li class="flex items-center gap-3 px-5 py-3" wire:key="due-{{ $d['kind'] }}-{{ $d['id'] }}">
                                @if ($d['icon'])
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-lg">{{ $d['icon'] }}</div>
                                @else
                                    <x-logo :institution="$d['institution']" />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold">{{ $d['name'] }}</span>
                                        @if ($d['kind'] === 'loan')<span class="badge-violet">Loan</span>@endif
                                        @if ($d['auto'])<span class="badge-slate">auto-pay</span>@endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $d['sub'] }} · {{ $d['date']->format('M j') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold tabular-nums">{{ money($d['amount']) }}</div>
                                    @if ($d['original'])<div class="text-[11px] text-slate-500">{{ $d['original'] }}</div>@endif
                                </div>
                                <x-due-badge :days="$d['days']" />
                                <button class="btn-primary btn-sm" wire:click="{{ $d['kind'] === 'bill' ? 'payBill' : 'payLoan' }}({{ $d['id'] }})"
                                        wire:confirm="Mark '{{ $d['name'] }}' as paid ({{ money($d['amount']) }})?">Paid</button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            <div class="card card-pad">
                <h2 class="font-bold">This month</h2>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-emerald-50 p-3">
                        <div class="text-[11px] font-semibold uppercase text-emerald-700">Income</div>
                        <div class="text-lg font-bold text-emerald-700 tabular-nums">{{ money($income) }}</div>
                    </div>
                    <div class="rounded-xl bg-red-50 p-3">
                        <div class="text-[11px] font-semibold uppercase text-red-700">Spent</div>
                        <div class="text-lg font-bold text-red-700 tabular-nums">{{ money($expenses) }}</div>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-500">Net this month: <span class="font-semibold {{ $income - $expenses >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ money($income - $expenses) }}</span> · USD rate ₱{{ number_format($usdRate, 2) }}</div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="font-bold">Budget & allowance</h2>
                    <a href="{{ route('budgets') }}" wire:navigate class="text-xs font-semibold text-brand-600">Manage</a>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($budgets as $b)
                        @php $used = $b->usedPercent(); $spent = $b->spent(); @endphp
                        <li class="px-5 py-3" wire:key="budget-{{ $b->id }}">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">{{ $b->icon }} {{ $b->name }} <span class="text-xs text-slate-400">/ {{ $b->period === 'weekly' ? 'week' : 'month' }}</span></span>
                                <span class="tabular-nums text-xs text-slate-500">{{ money($spent) }} / {{ money($b->amount) }}</span>
                            </div>
                            <x-progress :percent="$used" class="mt-1.5" />
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-xs text-slate-500">No budgets yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="font-bold">Savings goals</h2>
                    <a href="{{ route('savings') }}" wire:navigate class="text-xs font-semibold text-brand-600">Manage</a>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($goals as $g)
                        <li class="px-5 py-3" wire:key="goal-{{ $g->id }}">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium">{{ $g->icon }} {{ $g->name }}</span>
                                <span class="text-xs font-semibold text-slate-600">{{ $g->progressPercent() }}%</span>
                            </div>
                            <x-progress :percent="$g->progressPercent()" color="#059669" class="mt-1.5" />
                            <div class="mt-1 text-[11px] text-slate-500">{{ money($g->saved_amount) }} of {{ money($g->target_amount) }}</div>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-xs text-slate-500">No goals yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Accounts --}}
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h2 class="font-bold">Accounts & wallets</h2>
                <a href="{{ route('accounts') }}" wire:navigate class="text-xs font-semibold text-brand-600">Manage</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($accounts->sortByDesc('balance') as $a)
                    <li class="flex items-center gap-3 px-5 py-3" wire:key="acct-{{ $a->id }}">
                        <x-logo :institution="$a->institution" size="h-8 w-8" rounded="rounded-lg" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium">{{ $a->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $a->institution->name }} · {{ $a->typeLabel() }}</div>
                        </div>
                        <div class="text-sm font-bold tabular-nums {{ $a->isLiability() ? 'text-red-600' : '' }}">{{ $a->isLiability() ? '-' : '' }}{{ money($a->balance) }}</div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-xs text-slate-500">No accounts yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- Loans --}}
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h2 class="font-bold">Loans & credit</h2>
                <a href="{{ route('loans') }}" wire:navigate class="text-xs font-semibold text-brand-600">Manage</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($loans as $l)
                    <li class="px-5 py-3" wire:key="loan-{{ $l->id }}">
                        <div class="flex items-center gap-3">
                            <x-logo :institution="$l->institution" size="h-8 w-8" rounded="rounded-lg" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium">{{ $l->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $l->institution->name }} · {{ money($l->monthly_payment) }}/mo</div>
                            </div>
                            <div class="text-sm font-bold tabular-nums text-red-600">{{ money($l->outstanding_balance) }}</div>
                        </div>
                        @if ((float) $l->principal > 0 && in_array($l->type, ['term', 'installment', 'bnpl']))
                            <x-progress :percent="$l->progressPercent()" color="#7c3aed" class="mt-2" height="h-1.5" />
                        @endif
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-xs text-slate-500">No active loans. 🎉</li>
                @endforelse
            </ul>
        </div>

        {{-- Recent transactions --}}
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                <h2 class="font-bold">Recent transactions</h2>
                <a href="{{ route('transactions') }}" wire:navigate class="text-xs font-semibold text-brand-600">View all</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($recent as $t)
                    <li class="flex items-center gap-3 px-5 py-2.5" wire:key="tx-{{ $t->id }}">
                        <x-logo :institution="$t->account?->institution" size="h-7 w-7" rounded="rounded-md" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm">{{ $t->description ?: ucfirst($t->category) }}</div>
                            <div class="text-[11px] text-slate-500">{{ $t->date->format('M j') }} · {{ str_replace('_', ' ', $t->category) }}</div>
                        </div>
                        <div class="text-sm font-semibold tabular-nums {{ $t->isInflow() ? 'text-emerald-600' : 'text-slate-800' }}">{{ $t->isInflow() ? '+' : '-' }}{{ money($t->amount) }}</div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-xs text-slate-500">No transactions yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
