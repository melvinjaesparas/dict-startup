@props(['title' => '', 'maxWidth' => 'max-w-lg'])
<div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center"
     x-data x-on:keydown.escape.window="$wire.closeForm()">
    <div class="w-full {{ $maxWidth }} rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closeForm()">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
            <button type="button" wire:click="closeForm" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="max-h-[75vh] overflow-y-auto px-5 py-4">
            {{ $slot }}
        </div>
    </div>
</div>
