@props(['label', 'value', 'sub' => null, 'tone' => 'slate'])
@php
    $tones = [
        'slate' => 'text-slate-900', 'green' => 'text-emerald-600', 'red' => 'text-red-600',
        'blue' => 'text-brand-600', 'amber' => 'text-amber-600', 'violet' => 'text-violet-600',
    ];
@endphp
<div {{ $attributes->merge(['class' => 'card card-pad']) }}>
    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</div>
    <div class="mt-1 text-2xl font-bold tabular-nums {{ $tones[$tone] ?? $tones['slate'] }}">{{ $value }}</div>
    @if ($sub)<div class="mt-1 text-xs text-slate-500">{{ $sub }}</div>@endif
</div>
