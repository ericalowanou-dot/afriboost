<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Administration') — AfriBoost</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $menu = [
        ['Pilotage', [
            ['admin.dashboard', 'admin.dashboard', 'home', 'Tableau de bord', null],
            ['admin.participations.*', 'admin.participations.index', 'clipboard-check', 'Vérifications', $adminBadges['queue'] ?? 0],
            ['admin.creators.*', 'admin.creators.index', 'users', 'Créateurs', $adminBadges['creators'] ?? 0],
        ]],
        ['Campagnes', [
            ['admin.campaigns.*', 'admin.campaigns.index', 'layers', 'Campagnes', null],
            ['admin.missions.*', 'admin.missions.index', 'flag', 'Missions', null],
            ['admin.campaign-requests.*', 'admin.campaign-requests.index', 'inbox', 'Demandes de marques', $adminBadges['requests'] ?? 0],
        ]],
        ['Finances', [
            ['admin.payouts.*', 'admin.payouts.index', 'banknote', 'Paiements', $adminBadges['payouts'] ?? 0],
            ['admin.activity.*', 'admin.activity.index', 'history', 'Historique des actions', null],
        ]],
    ];
@endphp
<body class="font-afriboost bg-slate-100 text-slate-900 antialiased" x-data="{ nav: false }">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        {{-- Barre latérale --}}
        <div x-cloak x-show="nav" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="nav = false"></div>
        <aside class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col bg-slate-950 text-slate-300 transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
               :class="nav && 'translate-x-0'">
            <div class="flex items-center justify-between px-5 py-5">
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col">
                    <x-logo light />
                    <span class="ml-10 mt-0.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Administration</span>
                </a>
                <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 lg:hidden" @click="nav = false" aria-label="Fermer le menu">
                    <x-icon name="x" />
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6">
                @foreach ($menu as [$section, $items])
                    <div>
                        <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $section }}</p>
                        <div class="space-y-0.5">
                            @foreach ($items as [$pattern, $route, $icon, $label, $badge])
                                @php $active = request()->routeIs($pattern); @endphp
                                <a href="{{ route($route) }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $active ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                                    <x-icon :name="$icon" class="h-[18px] w-[18px] {{ $active ? 'text-brand-400' : 'text-slate-500' }}" />
                                    <span class="flex-1">{{ $label }}</span>
                                    @if ($badge)
                                        <span class="rounded-full bg-cta-600 px-2 py-0.5 text-[11px] font-bold text-white">{{ $badge }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-3">
                <a href="{{ route('missions.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold hover:bg-white/5 hover:text-white">
                    <x-icon name="smartphone" class="h-[18px] w-[18px] text-slate-500" /> Voir l'app créateur
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-cta-400 hover:bg-white/5">
                        <x-icon name="logout" class="h-[18px] w-[18px]" /> Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:px-6">
                <button type="button" class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="nav = true" aria-label="Ouvrir le menu">
                    <x-icon name="menu" />
                </button>
                <h1 class="min-w-0 flex-1 truncate text-lg font-extrabold sm:text-xl">@yield('heading', 'Administration')</h1>
                @hasSection('actions')
                    <div class="flex shrink-0 items-center gap-2">@yield('actions')</div>
                @endif
                <div class="hidden items-center gap-2 border-l border-slate-200 pl-3 sm:flex">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="hidden text-sm font-semibold text-slate-700 xl:block">{{ auth()->user()->name }}</span>
                </div>
            </header>

            <main class="p-4 sm:p-6">
                <x-flash class="mb-4" />
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
