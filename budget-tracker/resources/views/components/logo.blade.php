@props(['institution' => null, 'size' => 'h-9 w-9', 'rounded' => 'rounded-xl'])
@if ($institution && $institution->logoUrl())
    <img src="{{ $institution->logoUrl() }}" alt="{{ $institution->name }}" class="{{ $size }} {{ $rounded }} shrink-0 object-cover shadow-sm" />
@elseif ($institution)
    <div class="{{ $size }} {{ $rounded }} flex shrink-0 items-center justify-center text-xs font-bold shadow-sm"
         style="background: {{ $institution->color }}; color: {{ $institution->text_color }}">{{ $institution->initials() }}</div>
@else
    <div class="{{ $size }} {{ $rounded }} flex shrink-0 items-center justify-center bg-slate-200 text-xs font-bold text-slate-600">?</div>
@endif
