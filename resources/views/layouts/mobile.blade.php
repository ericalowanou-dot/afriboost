<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <title>@yield('title', 'AfriBoost')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-afriboost antialiased text-slate-900">
    <div class="min-h-screen bg-afriboost pb-24">
        <header class="sticky top-0 z-30 border-b border-white/40 bg-white/70 backdrop-blur-md">
            <div class="mx-auto flex max-w-lg items-center justify-between px-4 py-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-teal-700">AfriBoost</p>
                    <h1 class="text-lg font-bold leading-tight">@yield('heading', 'Missions')</h1>
                </div>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full bg-slate-900 px-3 py-2 text-xs font-bold text-white">Admin</a>
                    @else
                        <a href="{{ route('creator.profile') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-700 text-sm font-bold text-white shadow">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </a>
                    @endif
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="rounded-full px-3 py-2 text-xs font-bold text-teal-700">Connexion</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-bold text-white">S'inscrire</a>
                    </div>
                @endauth
            </div>
        </header>

        <main class="mx-auto max-w-lg px-4 py-4">
            @if (session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        @auth
            @unless(auth()->user()->isAdmin())
                <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-rose-100 bg-white/95 backdrop-blur">
                    <div class="mx-auto grid max-w-lg grid-cols-4 gap-1 px-2 py-2 text-[11px] font-semibold text-slate-500">
                        <a href="{{ route('missions.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 {{ request()->routeIs('missions.*') ? 'bg-rose-50 text-rose-600' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M3 12h18M3 17h18"/></svg>
                            Accueil
                        </a>
                        <a href="{{ route('participations.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 {{ request()->routeIs('participations.*') ? 'bg-rose-50 text-rose-600' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Activité
                        </a>
                        <a href="{{ route('wallet.index') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 {{ request()->routeIs('wallet.*') ? 'bg-rose-50 text-rose-600' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Wallet
                        </a>
                        <a href="{{ route('creator.profile') }}" class="flex flex-col items-center gap-1 rounded-xl px-2 py-2 {{ request()->routeIs('creator.*') ? 'bg-rose-50 text-rose-600' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profil
                        </a>
                    </div>
                </nav>
            @endunless
        @endauth
    </div>
</body>
</html>
