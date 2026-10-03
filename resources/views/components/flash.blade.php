@props(['bag' => 'default'])
@php
    $messages = collect([
        ['success', session('success'), 'check-circle', 'bg-brand-50 text-brand-800 ring-brand-600/20'],
        ['warning', session('warning'), 'alert', 'bg-amber-50 text-amber-800 ring-amber-600/20'],
        ['info', session('status') && ! str_contains((string) session('status'), '-') ? session('status') : null, 'info', 'bg-sky-50 text-sky-800 ring-sky-600/20'],
    ])->filter(fn ($m) => filled($m[1]));
    $errorBag = $errors->getBag($bag);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @foreach ($messages as [$type, $text, $icon, $tone])
        <div x-data="{ show: true }" x-show="show" x-transition.opacity
             class="flex animate-fade-in items-start gap-3 rounded-2xl px-4 py-3 text-sm ring-1 {{ $tone }}" role="status">
            <x-icon :name="$icon" class="mt-0.5 h-5 w-5" />
            <p class="flex-1">{{ $text }}</p>
            <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Fermer">
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </div>
    @endforeach

    @if ($errorBag->any())
        <div class="flex animate-fade-in items-start gap-3 rounded-2xl bg-cta-50 px-4 py-3 text-sm text-cta-700 ring-1 ring-cta-600/20" role="alert">
            <x-icon name="alert" class="mt-0.5 h-5 w-5" />
            <ul class="flex-1 space-y-1">
                @foreach ($errorBag->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
