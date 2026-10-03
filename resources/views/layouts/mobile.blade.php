<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AfriBoost')</title>
    <meta name="description" content="AfriBoost — missions de promotion rémunérées en USD pour les créateurs de contenu.">
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $authUser = auth()->user();
    $navItems = [
        ['missions.*', 'missions.index', 'flag', 'Missions'],
        ['participations.*', 'participations.index', 'clipboard-check', 'Activité'],
        ['wallet.*', 'wallet.index', 'wallet', 'Wallet'],
        ['creator.*', 'creator.profile', 'user', 'Profil'],
    ];
@endphp
<body class="font-afriboost bg-afriboost text-slate-900 antialiased" x-data="{ drawer: false }" @keydown.escape.window="drawer = false">
    <div class="min-h-screen {{ $authUser && ! $authUser->isAdmin() ? 'pb-28' : 'pb-24' }}">
        {{-- Barre supérieure : menu, logo, avatar --}}
        <header class="sticky top-0 z-30 transition-colors" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 8"
                :class="scrolled ? 'bg-[#d4efe2]/85 shadow-sm backdrop-blur-md' : 'bg-transparent'">
            <div class="mx-auto flex max-w-lg items-center justify-between px-4 pb-2 pt-3">
                <button type="button" @click="drawer = true" class="-ml-2 rounded-xl p-2 text-slate-800 hover:bg-white/50" aria-label="Ouvrir le menu">
                    <x-icon name="menu" class="h-6 w-6" stroke="2.2" />
                </button>
                <a href="{{ route('missions.index') }}" aria-label="Accueil AfriBoost">
                    <x-logo class="scale-90" />
                </a>
                @auth
                    <a href="{{ $authUser->isAdmin() ? route('admin.dashboard') : route('creator.profile') }}"
                       class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-extrabold text-white shadow-md ring-2 ring-white"
                       aria-label="Mon profil">
                        {{ mb_strtoupper(mb_substr($authUser->name, 0, 1)) }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-white/80 px-3 py-2 text-xs font-bold text-brand-700 shadow-sm">Connexion</a>
                @endauth
            </div>
        </header>

        <main class="mx-auto max-w-lg px-4">
            <div class="mb-4 mt-1 flex items-center gap-2">
                @hasSection('back')
                    <a href="@yield('back')" class="-ml-1 rounded-xl p-1.5 text-slate-800 hover:bg-white/50" aria-label="Retour">
                        <x-icon name="arrow-left" class="h-6 w-6" stroke="2.2" />
                    </a>
                @endif
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">@yield('heading', 'Missions')</h1>
            </div>

            <x-flash class="mb-4" />

            @yield('content')
        </main>

        {{-- Navigation basse persistante (cahier des charges §6) --}}
        @auth
            @unless ($authUser->isAdmin())
                <nav class="fixed inset-x-0 bottom-0 z-40 px-3 pb-safe" aria-label="Navigation principale">
                    <div class="mx-auto grid max-w-lg grid-cols-4 rounded-3xl border-2 border-dashed border-cta-400/40 bg-white/95 p-1.5 shadow-nav backdrop-blur">
                        @foreach ($navItems as [$pattern, $route, $icon, $label])
                            @php $active = request()->routeIs($pattern); @endphp
                            <a href="{{ route($route) }}"
                               class="flex flex-col items-center gap-1 rounded-2xl py-2 text-[11px] font-bold transition {{ $active ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:text-slate-900' }}"
                               @if ($active) aria-current="page" @endif>
                                <x-icon :name="$icon" class="h-6 w-6" :stroke="$active ? 2.2 : 1.9" />
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </nav>
            @endunless
        @else
            <div class="fixed inset-x-0 bottom-0 z-40 px-4 pb-safe">
                <div class="mx-auto flex max-w-lg gap-2">
                    <a href="{{ route('login') }}" class="ab-btn-cta flex-1 rounded-full py-3.5 text-base">Se connecter</a>
                    <a href="{{ route('register') }}" class="ab-btn flex-1 rounded-full bg-white py-3.5 text-base text-brand-700 shadow-card">Créer un compte</a>
                </div>
            </div>
        @endauth
    </div>

    {{-- Menu latéral --}}
    <div x-cloak x-show="drawer" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Menu">
        <div x-show="drawer" x-transition.opacity class="absolute inset-0 bg-slate-900/40" @click="drawer = false"></div>
        <aside x-show="drawer"
               x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="absolute inset-y-0 left-0 flex w-72 max-w-[85%] flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <x-logo />
                <button type="button" @click="drawer = false" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100" aria-label="Fermer le menu">
                    <x-icon name="x" />
                </button>
            </div>

            @auth
                <div class="flex items-center gap-3 px-5 py-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-600 font-extrabold text-white">
                        {{ mb_strtoupper(mb_substr($authUser->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-bold">{{ $authUser->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $authUser->email }}</p>
                    </div>
                </div>
            @endauth

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2 text-sm font-semibold">
                @auth
                    @if ($authUser->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-50">
                            <x-icon name="shield" class="text-slate-400" /> Administration
                        </a>
                    @endif
                    @foreach ($navItems as [$pattern, $route, $icon, $label])
                        <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 {{ request()->routeIs($pattern) ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                            <x-icon :name="$icon" class="{{ request()->routeIs($pattern) ? 'text-brand-600' : 'text-slate-400' }}" />
                            {{ $label === 'Activité' ? 'Mes participations' : ($label === 'Wallet' ? 'Mon wallet' : ($label === 'Profil' ? 'Mon profil' : $label)) }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('missions.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-50">
                        <x-icon name="flag" class="text-slate-400" /> Missions
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-50">
                        <x-icon name="user" class="text-slate-400" /> Connexion
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-50">
                        <x-icon name="users-plus" class="text-slate-400" /> Créer un compte
                    </a>
                @endauth

                <div class="my-2 border-t border-slate-100"></div>
                <a href="{{ route('brands.create') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 hover:bg-slate-50">
                    <x-icon name="megaphone" class="text-slate-400" /> Lancer une campagne
                </a>
                <button type="button" x-show="$store.pwa.canInstall" x-cloak @click="$store.pwa.install()" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-slate-700 hover:bg-slate-50">
                    <x-icon name="smartphone" class="text-slate-400" /> Installer l'application
                </button>
            </nav>

            @auth
                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 p-3">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-cta-600 hover:bg-cta-50">
                        <x-icon name="logout" /> Se déconnecter
                    </button>
                </form>
            @endauth
        </aside>
    </div>

    @stack('scripts')
</body>
</html>
