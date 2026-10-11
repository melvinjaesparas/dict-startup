@props(['title' => 'Nothing here yet', 'text' => null])
<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 px-6 py-12 text-center">
    <div class="text-3xl">🗂️</div>
    <div class="mt-2 text-sm font-semibold text-slate-700">{{ $title }}</div>
    @if ($text)<div class="mt-1 max-w-sm text-xs text-slate-500">{{ $text }}</div>@endif
    <div class="mt-4">{{ $slot }}</div>
</div>
