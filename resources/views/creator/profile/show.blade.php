@extends('layouts.mobile')

@section('title', 'Profil — AfriBoost')
@section('heading', 'Profil')

@section('content')
    <section class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <h2 class="font-bold">Mon compte</h2>
        <form method="POST" action="{{ route('creator.profile.update') }}" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-sm font-semibold">Nom</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm">
            </div>
            <button class="w-full rounded-2xl bg-teal-700 py-3 font-bold text-white">Enregistrer</button>
        </form>
    </section>

    <section class="mt-4 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <h2 class="font-bold">Mes réseaux sociaux</h2>
        <div class="mt-3 space-y-2">
            @forelse ($user->socialNetworks as $network)
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-3 text-sm">
                    <div>
                        <p class="font-semibold">{{ $network->platformLabel() }}</p>
                        <p class="text-slate-500">{{ $network->handle ?: $network->profile_url }}</p>
                    </div>
                    <form method="POST" action="{{ route('creator.networks.destroy', $network) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-rose-600 font-semibold">Suppr.</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Ajoute tes comptes pour faciliter les vérifications.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('creator.networks.store') }}" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-semibold">Plateforme</label>
                <select name="platform" class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm">
                    <option value="tiktok">TikTok</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="youtube">YouTube</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Pseudo / handle</label>
                <input type="text" name="handle" class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm" placeholder="@toncompte">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Lien du profil</label>
                <input type="url" name="profile_url" class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm" placeholder="https://...">
            </div>
            <button class="w-full rounded-2xl bg-rose-600 py-3 font-bold text-white">Ajouter le réseau</button>
        </form>
    </section>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="w-full rounded-2xl border border-slate-200 bg-white py-3 font-semibold text-slate-600">Se déconnecter</button>
    </form>

    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="mt-3 block text-center text-sm font-semibold text-teal-700">Aller à l'administration →</a>
    @endif
@endsection
