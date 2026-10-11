@props(['percent' => 0, 'color' => null, 'height' => 'h-2'])
@php
    $percent = max(0, min(100, (int) $percent));
    $color = $color ?: ($percent >= 100 ? '#dc2626' : ($percent >= 80 ? '#d97706' : '#2563eb'));
@endphp
<div {{ $attributes->merge(['class' => "w-full overflow-hidden rounded-full bg-slate-100 $height"]) }}>
    <div class="{{ $height }} rounded-full transition-all" style="width: {{ $percent }}%; background: {{ $color }}"></div>
</div>
