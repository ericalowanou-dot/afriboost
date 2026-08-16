<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin AfriBoost')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-afriboost bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[240px_1fr]">
        <aside class="border-b border-slate-200 bg-slate-900 text-white lg:border-b-0 lg:border-r lg:border-slate-800">
            <div class="px-5 py-5">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal-300">AfriBoost</p>
                <p class="mt-1 text-lg font-bold">Administration</p>
            </div>
            <nav class="flex gap-2 overflow-x-auto px-3 pb-4 lg:flex-col lg:overflow-visible">
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2 text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-white/10' : 'hover:bg-white/5' }}">Tableau de bord</a>
                <a href="{{ route('admin.missions.index') }}" class="rounded-xl px-3 py-2 text-sm {{ request()->routeIs('admin.missions.*') ? 'bg-white/10' : 'hover:bg-white/5' }}">Missions</a>
                <a href="{{ route('admin.participations.index') }}" class="rounded-xl px-3 py-2 text-sm {{ request()->routeIs('admin.participations.*') ? 'bg-white/10' : 'hover:bg-white/5' }}">Vérifications</a>
                <a href="{{ route('admin.creators.index') }}" class="rounded-xl px-3 py-2 text-sm {{ request()->routeIs('admin.creators.*') ? 'bg-white/10' : 'hover:bg-white/5' }}">Créateurs</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2 lg:mt-6">
                    @csrf
                    <button class="rounded-xl px-3 py-2 text-sm text-rose-300 hover:bg-white/5">Déconnexion</button>
                </form>
            </nav>
        </aside>

        <div>
            <header class="border-b border-slate-200 bg-white px-6 py-4">
                <h1 class="text-xl font-bold">@yield('heading', 'Admin')</h1>
            </header>
            <main class="p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
