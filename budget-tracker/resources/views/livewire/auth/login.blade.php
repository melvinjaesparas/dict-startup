<div class="w-full max-w-sm">
    <div class="mb-6 flex items-center justify-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500 text-2xl font-bold text-white shadow-lg shadow-brand-500/40">₱</div>
        <div>
            <div class="text-lg font-bold text-white">Astin Budget</div>
            <div class="text-xs text-slate-300">Justine Tabunan · Astin Tech Solutions</div>
        </div>
    </div>

    <form wire:submit="login" class="card card-pad space-y-4">
        <x-field label="Email" for="email">
            <input id="email" type="email" wire:model="email" class="input" autocomplete="username" autofocus placeholder="you@example.com">
        </x-field>
        <x-field label="Password" for="password">
            <input id="password" type="password" wire:model="password" class="input" autocomplete="current-password" placeholder="••••••••">
        </x-field>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Keep me signed in
        </label>
        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in…</span>
        </button>
    </form>
    <p class="mt-4 text-center text-[11px] text-slate-400">Private budget tracker. Not for public sign-ups.</p>
</div>
