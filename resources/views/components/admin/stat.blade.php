@props(['label', 'value', 'icon', 'tone' => 'slate', 'href' => null, 'hint' => null])
@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-600',
        'brand' => 'bg-brand-50 text-brand-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'cta' => 'bg-cta-50 text-cta-600',
        'sky' => 'bg-sky-50 text-sky-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'ab-panel block p-5 transition '.($href ? 'hover:ring-slate-900/10 hover:shadow-md' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['slate'] }}">
            <x-icon :name="$icon" class="h-[18px] w-[18px]" />
        </span>
    </div>
    <p class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs font-medium text-slate-500">{{ $hint }}</p>
    @endif
</{{ $tag }}>
