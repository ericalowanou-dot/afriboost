@extends('layouts.mobile')

@section('title', 'Mon profil — AfriBoost')
@section('heading', 'Mon profil')

@php
    use App\Support\Money;

    $passwordErrors = $errors->getBag('updatePassword');
    $verification = [
        'verified' => ['Compte vérifié', 'bg-brand-100 text-brand-700', 'shield'],
        'rejected' => ['Compte refusé', 'bg-cta-100 text-cta-700', 'x-circle'],
        'pending' => ['En vérification', 'bg-amber-100 text-amber-800', 'clock'],
    ][$user->verification_status] ?? ['En vérification', 'bg-amber-100 text-amber-800', 'clock'];
    $connected = $user->socialNetworks->pluck('platform')->all();
@endphp

@section('content')
    {{-- Identité --}}
    <section class="ab-card p-4">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-2xl font-extrabold text-white ring-4 ring-brand-50">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-lg font-extrabold">{{ $user->name }}</p>
                <div class="mt-1 flex flex-wrap gap-1.5">
                    <span class="ab-chip {{ $verification[1] }}"><x-icon :name="$verification[2]" class="h-3.5 w-3.5" /> {{ $verification[0] }}</span>
                    @if ($user->creator_tier)
                        <span class="ab-chip bg-slate-900 text-white"><x-icon name="star" class="h-3 w-3 text-amber-400" /> {{ $user->creatorTierLabel() }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-3 divide-x divide-slate-100 rounded-2xl bg-slate-50 py-3 text-center">
            <div><p class="text-lg font-extrabold">{{ $stats['participations'] }}</p><p class="text-[11px] font-semibold text-slate-500">Missions</p></div>
            <div><p class="text-lg font-extrabold">{{ $stats['validated'] }}</p><p class="text-[11px] font-semibold text-slate-500">Validées</p></div>
            <div><p class="text-lg font-extrabold text-brand-600">{{ Money::usd($stats['earned']) }}</p><p class="text-[11px] font-semibold text-slate-500">Gagnés</p></div>
        </div>
    </section>

    @if ($user->verification_status === 'rejected')
        <section class="mt-3 rounded-3xl bg-cta-50 p-4 text-sm text-cta-800 ring-1 ring-cta-200">
            <p class="font-bold">Ton compte a été refusé</p>
            @if ($user->status_reason)
                <p class="mt-1">Motif : {{ $user->status_reason }}</p>
            @endif
            <p class="mt-1">Corrige tes informations ou tes réseaux ci-dessous, puis redemande une vérification.</p>
            <form method="POST" action="{{ route('creator.profile.request-verification') }}" class="mt-3">
                @csrf
                <button class="ab-btn-cta w-full">Redemander une vérification</button>
            </form>
        </section>
    @elseif ($user->verification_status !== 'verified')
        <section class="mt-3 flex gap-3 rounded-3xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
            <x-icon name="clock" class="mt-0.5" />
            <p>L'équipe AfriBoost vérifie ton compte et tes réseaux. Tu pourras participer aux missions dès la validation.</p>
        </section>
    @endif

    {{-- Réseaux sociaux --}}
    <section id="reseaux" class="ab-card mt-3 scroll-mt-20 p-4" x-data="{ adding: {{ $errors->has('platform') || $errors->has('profile_url') || $user->socialNetworks->isEmpty() ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold">Mes réseaux sociaux</h2>
            <button type="button" @click="adding = !adding" class="inline-flex items-center gap-1 text-sm font-bold text-brand-700">
                <x-icon name="plus" class="h-4 w-4" stroke="2.6" /> Ajouter
            </button>
        </div>

        <div class="mt-3 space-y-2">
            @forelse ($user->socialNetworks as $network)
                <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm"><x-network-icon :network="$network->platform" class="h-5 w-5" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold">{{ $network->platformLabel() }}</p>
                        <p class="truncate text-xs text-slate-500">
                            {{ $network->handle ?: $network->profile_url }}
                            @if ($network->follower_count)
                                · {{ number_format($network->follower_count, 0, ',', ' ') }} abonnés
                            @endif
                        </p>
                    </div>
                    @if ($network->creator_tier)
                        <span class="ab-chip bg-white text-slate-700 ring-1 ring-slate-200">{{ $network->tierLabel() }}</span>
                    @endif
                    <form method="POST" action="{{ route('creator.networks.destroy', $network) }}"
                          onsubmit="return confirm('Retirer ce réseau de ton profil ?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg p-1.5 text-slate-400 hover:bg-cta-50 hover:text-cta-600" aria-label="Supprimer {{ $network->platformLabel() }}">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            @empty
                <p class="rounded-2xl bg-slate-50 px-3 py-4 text-center text-sm text-slate-500">Ajoute tes comptes pour pouvoir participer aux missions.</p>
            @endforelse
        </div>

        <form x-show="adding" x-cloak x-transition method="POST" action="{{ route('creator.networks.store') }}" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
            @csrf
            <div>
                <span class="ab-label">Plateforme</span>
                <div class="grid grid-cols-4 gap-2">
                    @foreach (\App\Models\Mission::NETWORK_LABELS as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="platform" value="{{ $value }}" class="peer sr-only" @checked(old('platform', collect(array_keys(\App\Models\Mission::NETWORK_LABELS))->first(fn ($p) => ! in_array($p, $connected, true)) ?? 'tiktok') === $value)>
                            <span class="flex flex-col items-center gap-1 rounded-2xl py-2.5 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:ring-2 peer-checked:ring-brand-500">
                                <x-network-icon :network="$value" class="h-5 w-5" /> {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label for="handle" class="ab-label">Pseudo</label>
                <input id="handle" type="text" name="handle" value="{{ old('handle') }}" class="ab-input" placeholder="@toncompte">
            </div>
            <div>
                <label for="profile_url" class="ab-label">Lien du profil</label>
                <input id="profile_url" type="url" name="profile_url" value="{{ old('profile_url') }}" class="ab-input" placeholder="https://www.tiktok.com/@toncompte" inputmode="url">
            </div>
            <button class="ab-btn-cta w-full">Enregistrer le réseau</button>
        </form>
    </section>

    {{-- Compte --}}
    <section class="ab-card mt-3 p-4">
        <h2 class="font-extrabold">Mon compte</h2>
        <form method="POST" action="{{ route('creator.profile.update') }}" class="mt-3 space-y-3">
            @csrf
            @method('PATCH')
            <div>
                <label for="name" class="ab-label">Nom complet</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="ab-input" autocomplete="name">
            </div>
            <div>
                <label for="email" class="ab-label">E-mail</label>
                <input id="email" type="email" value="{{ $user->email }}" disabled class="ab-input bg-slate-50 text-slate-500">
            </div>
            <div>
                <label for="phone" class="ab-label">Numéro WhatsApp</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="ab-input" autocomplete="tel">
            </div>
            <button class="ab-btn-brand w-full">Enregistrer</button>
        </form>
    </section>

    {{-- Mot de passe --}}
    <section class="ab-card mt-3 p-4" x-data="{ open: {{ $passwordErrors->any() ? 'true' : 'false' }} }">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
            <span class="flex items-center gap-2 font-extrabold"><x-icon name="lock" class="h-[18px] w-[18px]" /> Mot de passe</span>
            <x-icon name="chevron-down" class="h-5 w-5 text-slate-400 transition" ::class="open && 'rotate-180'" />
        </button>
        @if (session('status') === 'password-updated')
            <p class="mt-2 text-sm font-semibold text-brand-700">Mot de passe mis à jour.</p>
        @endif
        <form x-show="open" x-cloak x-transition method="POST" action="{{ route('password.update') }}" class="mt-3 space-y-3">
            @csrf
            @method('PUT')
            @if ($passwordErrors->any())
                <div class="rounded-2xl bg-cta-50 px-3 py-2 text-sm text-cta-700">
                    @foreach ($passwordErrors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label for="current_password" class="ab-label">Mot de passe actuel</label>
                <input id="current_password" type="password" name="current_password" class="ab-input" autocomplete="current-password" required>
            </div>
            <div>
                <label for="password" class="ab-label">Nouveau mot de passe</label>
                <input id="password" type="password" name="password" class="ab-input" autocomplete="new-password" required>
            </div>
            <div>
                <label for="password_confirmation" class="ab-label">Confirmation</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="ab-input" autocomplete="new-password" required>
            </div>
            <button class="ab-btn-brand w-full">Changer le mot de passe</button>
        </form>
    </section>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button class="ab-btn-ghost w-full text-cta-600"><x-icon name="logout" class="h-[18px] w-[18px]" /> Se déconnecter</button>
    </form>
@endsection
