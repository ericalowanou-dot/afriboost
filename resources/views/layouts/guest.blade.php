<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AfriBoost' }}</title>
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-afriboost bg-afriboost text-slate-900 antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="px-4 pb-5 pt-8 text-center">
            <a href="{{ route('missions.index') }}" class="inline-flex flex-col items-center gap-3">
                <x-logo class="scale-125" />
                <span class="mt-1 text-sm font-semibold text-slate-600">Monétise ton audience</span>
            </a>
        </header>

        <main class="flex-1 px-4 pb-10">
            <div class="mx-auto w-full max-w-md">
                <x-flash class="mb-4" />

                <div class="ab-card p-6">
                    {{ $slot }}
                </div>

                <div class="mt-6 flex items-center justify-center gap-3">
                    @foreach (['tiktok', 'instagram', 'facebook', 'youtube'] as $n)
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/80 shadow-sm"><x-network-icon :network="$n" class="h-[18px] w-[18px]" /></span>
                    @endforeach
                </div>
                <p class="mt-3 text-center text-xs text-slate-500">Missions de promotion rémunérées en USD</p>
            </div>
        </main>
    </div>
</body>
</html>
