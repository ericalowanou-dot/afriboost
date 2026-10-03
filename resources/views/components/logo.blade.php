@props(['wordmark' => true, 'light' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <svg class="h-8 w-8 shrink-0" viewBox="0 0 64 64" aria-hidden="true">
        <defs>
            <linearGradient id="ab-logo-g" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#2fb36a"/>
                <stop offset="1" stop-color="#14532d"/>
            </linearGradient>
        </defs>
        <rect width="64" height="64" rx="16" fill="url(#ab-logo-g)"/>
        <path d="M20 46 32 16l12 30" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M24.5 36h15" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
        <circle cx="47" cy="17" r="5" fill="#ef3b31"/>
    </svg>
    @if ($wordmark)
        <span class="text-lg font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-slate-900' }}">Afri<span class="{{ $light ? 'text-brand-300' : 'text-brand-600' }}">Boost</span></span>
    @endif
</span>
