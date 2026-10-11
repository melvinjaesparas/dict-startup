@props(['label', 'for' => null, 'error' => null, 'hint' => null])
<div {{ $attributes->merge(['class' => '']) }}>
    <label @if($for) for="{{ $for }}" @endif class="label">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
    @error($error ?? $for)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
