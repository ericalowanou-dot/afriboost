<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <title>{{ $title ?? 'AfriBoost' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-afriboost antialiased text-slate-900">
    <div class="min-h-screen bg-afriboost flex flex-col">
        <header class="px-4 pt-8 pb-4 text-center">
            <a href="{{ route('missions.index') }}" class="inline-block">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-700">AfriBoost</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">Monétise ton audience</p>
            </a>
        </header>

        <main class="flex-1 px-4 pb-8">
            <div class="mx-auto w-full max-w-md">
                @if (session('status'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-100">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">
                    Missions promo · TikTok · Instagram · YouTube · Facebook
                </p>
            </div>
        </main>
    </div>
</body>
</html>
