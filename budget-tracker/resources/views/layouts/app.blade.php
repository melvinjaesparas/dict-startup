<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-900 antialiased" x-data="{ sidebarOpen: false }">
@php
    $profiles = \App\Models\Profile::orderBy('id')->get();
    $currentProfileId = current_profile_id();
    $currentProfile = $currentProfileId ? $profiles->firstWhere('id', $currentProfileId) : null;
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h4v-6h6v6h4V10'],
        ['route' => 'accounts', 'label' => 'Accounts & Wallets', 'icon' => 'M3 7h18v10H3zM3 11h18M7 15h3'],
        ['route' => 'loans', 'label' => 'Loans & Credit', 'icon' => 'M12 3v18M7 8h7a3 3 0 010 6H8a3 3 0 000 6h9'],
        ['route' => 'bills', 'label' => 'Bills & Dues', 'icon' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6'],
        ['route' => 'subscriptions', 'label' => 'Subscriptions', 'icon' => 'M4 12a8 8 0 0114-5.3M20 12a8 8 0 01-14 5.3M18 4v4h-4M6 20v-4h4'],
        ['route' => 'transactions', 'label' => 'Transactions', 'icon' => 'M4 7h16M4 12h10M4 17h7'],
        ['route' => 'savings', 'label' => 'Savings Goals', 'icon' => 'M5 10a7 7 0 0114 0v4a3 3 0 01-3 3H8a3 3 0 01-3-3zM9 10h.01M3 12H5'],
        ['route' => 'budgets', 'label' => 'Budget & Allowance', 'icon' => 'M12 3a9 9 0 109 9h-9z M12 3v9h9'],
        ['route' => 'institutions', 'label' => 'Banks & E-wallets', 'icon' => 'M3 10l9-6 9 6M5 10v9M19 10v9M9 10v9M15 10v9M3 19h18'],
        ['route' => 'settings', 'label' => 'Settings', 'icon' => 'M12 8a4 4 0 100 8 4 4 0 000-8zM4 12h2M18 12h2M12 4v2M12 18v2M6.3 6.3l1.4 1.4M16.3 16.3l1.4 1.4M6.3 17.7l1.4-1.4M16.3 7.7l1.4-1.4'],
    ];
@endphp

<div class="flex min-h-full">
    {{-- Sidebar --}}
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="sidebarOpen = false" style="display:none"></div>
    <aside class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-ink-900 text-slate-200 transition-transform lg:static lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex items-center gap-3 px-5 py-5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-lg font-bold text-white shadow-lg shadow-brand-500/30">₱</div>
            <div>
                <div class="text-sm font-bold text-white">Astin Budget</div>
                <div class="text-[11px] text-slate-400">Developer budget tracker</div>
            </div>
        </div>

        {{-- Profile switcher --}}
        <form method="POST" action="{{ route('profile.switch') }}" class="px-4 pb-3">
            @csrf
            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-widest text-slate-500">Viewing</label>
            <select name="profile_id" onchange="this.form.submit()" class="w-full rounded-xl border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <option value="all" @selected(! $currentProfileId)>All profiles</option>
                @foreach ($profiles as $p)
                    <option value="{{ $p->id }}" @selected($currentProfileId === $p->id)>{{ $p->name }} ({{ $p->isBusiness() ? 'Business' : 'Personal' }})</option>
                @endforeach
            </select>
        </form>

        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-2">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" wire:navigate class="nav-link {{ request()->routeIs($item['route']) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $item['icon'] }}"/></svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 px-4 py-4">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                    <div class="truncate text-[11px] text-slate-400">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white" title="Log out">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5M15 12H3M21 4v16"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur lg:px-8">
            <button class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <h1 class="text-base font-bold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
            <div class="ml-auto flex items-center gap-2">
                @if ($currentProfile)
                    <span class="badge" style="background: {{ $currentProfile->color }}1a; color: {{ $currentProfile->color }}">{{ $currentProfile->name }}</span>
                @else
                    <span class="badge-slate">All profiles</span>
                @endif
                <span class="hidden text-xs text-slate-500 sm:inline">{{ now()->format('D, M j, Y') }}</span>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Toasts --}}
<div x-data="{ toasts: [] }"
     x-on:toast.window="const t = { id: Date.now(), ...$event.detail }; toasts.push(t); setTimeout(() => toasts = toasts.filter(x => x.id !== t.id), 3500)"
     class="pointer-events-none fixed bottom-4 right-4 z-50 flex flex-col gap-2">
    <template x-for="t in toasts" :key="t.id">
        <div x-transition class="pointer-events-auto rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
             :class="t.type === 'error' ? 'bg-red-600' : 'bg-slate-900'" x-text="t.message"></div>
    </template>
</div>
</body>
</html>
