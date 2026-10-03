@props(['mission'])
@if ($mission->logoUrl())
    <img src="{{ $mission->logoUrl() }}" alt="{{ $mission->brand_name }}" loading="lazy"
         {{ $attributes->merge(['class' => 'shrink-0 rounded-2xl object-cover ring-1 ring-slate-900/5']) }}>
@else
    <div {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-800 p-2 text-center']) }}>
        <span class="text-sm font-black uppercase leading-tight tracking-wide text-white [overflow-wrap:anywhere]">{{ $mission->brand_name }}</span>
    </div>
@endif
