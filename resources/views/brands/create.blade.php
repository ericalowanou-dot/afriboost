@extends('layouts.mobile')

@section('title', 'Lancer une campagne — AfriBoost')
@section('heading', 'Lancer une campagne')
@section('back', route('missions.index'))

@section('content')
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 to-cta-600 p-5 text-white shadow-card">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <span class="ab-chip relative bg-white/20 text-white"><x-icon name="megaphone" class="h-3.5 w-3.5" /> Pour les marques</span>
        <p class="relative mt-3 text-xl font-extrabold leading-tight">Faites promouvoir vos produits par des créateurs sélectionnés</p>
        <p class="relative mt-2 text-sm text-white/85">Nous préparons la campagne, publions les missions et vérifions chaque contenu avant paiement.</p>
    </section>

    <section class="mt-3 grid grid-cols-3 gap-2 text-center">
        @foreach ([['users', 'Créateurs vérifiés'], ['shield', 'Contenus contrôlés'], ['chart', 'Suivi des résultats']] as [$icon, $label])
            <div class="ab-card px-2 py-3">
                <x-icon :name="$icon" class="mx-auto h-6 w-6 text-brand-600" />
                <p class="mt-1.5 text-[11px] font-bold leading-tight text-slate-700">{{ $label }}</p>
            </div>
        @endforeach
    </section>

    <form method="POST" action="{{ route('brands.store') }}" class="ab-card mt-3 space-y-4 p-4">
        @csrf
        <h2 class="font-extrabold">Parlez-nous de votre projet</h2>

        {{-- Pot de miel : laissé vide par les humains --}}
        <div class="hidden" aria-hidden="true">
            <label for="website">Site web</label>
            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <label for="company_name" class="ab-label">Marque / entreprise</label>
            <input id="company_name" name="company_name" value="{{ old('company_name') }}" required class="ab-input" autocomplete="organization">
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="contact_name" class="ab-label">Votre nom</label>
                <input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required class="ab-input" autocomplete="name">
            </div>
            <div>
                <label for="phone" class="ab-label">Téléphone / WhatsApp</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="ab-input" autocomplete="tel">
            </div>
        </div>
        <div>
            <label for="email" class="ab-label">E-mail professionnel</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ab-input" autocomplete="email" inputmode="email">
        </div>
        <div>
            <label for="objective" class="ab-label">Objectif de la campagne</label>
            <textarea id="objective" name="objective" rows="4" required class="ab-input" placeholder="Ex. faire connaître notre nouvelle application auprès des 18-30 ans à Lomé.">{{ old('objective') }}</textarea>
        </div>
        <div>
            <span class="ab-label">Réseaux souhaités</span>
            <div class="grid grid-cols-4 gap-2">
                @foreach (\App\Models\Mission::NETWORK_LABELS as $value => $label)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="networks[]" value="{{ $value }}" class="peer sr-only" @checked(in_array($value, old('networks', []), true))>
                        <span class="flex flex-col items-center gap-1 rounded-2xl py-2.5 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:ring-2 peer-checked:ring-brand-500">
                            <x-network-icon :network="$value" class="h-5 w-5" /> {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="budget_usd" class="ab-label">Budget (USD)</label>
                <input id="budget_usd" name="budget_usd" type="number" min="0" step="50" value="{{ old('budget_usd') }}" class="ab-input" placeholder="500">
            </div>
            <div>
                <label for="desired_start" class="ab-label">Démarrage</label>
                <input id="desired_start" name="desired_start" type="date" min="{{ now()->toDateString() }}" value="{{ old('desired_start') }}" class="ab-input">
            </div>
        </div>
        <button class="ab-btn-cta w-full py-4 text-base">Envoyer ma demande</button>
        <p class="text-center text-xs text-slate-500">Vos informations servent uniquement à vous recontacter.</p>
    </form>
@endsection
