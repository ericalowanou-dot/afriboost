<x-guest-layout title="Créer mon compte — AfriBoost">
    <div class="mb-6">
        <div class="flex items-center gap-2">
            <span class="h-1.5 flex-1 rounded-full bg-brand-600"></span>
            <span class="h-1.5 flex-1 rounded-full bg-slate-200"></span>
        </div>
        <p class="mt-3 text-xs font-bold uppercase tracking-wider text-brand-700">Étape 1 sur 2</p>
        <h1 class="mt-1 text-xl font-extrabold">Créer mon compte</h1>
        <p class="mt-1 text-sm text-slate-500">Rejoins AfriBoost et commence à gagner en USD.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="name" class="ab-label">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="ab-input">
        </div>

        <div>
            <label for="email" class="ab-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" inputmode="email" class="ab-input">
        </div>

        <div>
            <label for="phone" class="ab-label">Numéro WhatsApp</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+228 90 00 00 00" class="ab-input">
        </div>

        <div>
            <label for="password" class="ab-label">Mot de passe</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" class="ab-input pr-12">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 hover:text-slate-600" :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                    <x-icon name="eye" class="h-[18px] w-[18px]" />
                </button>
            </div>
            <p class="mt-1 text-xs text-slate-400">Choisissez le mot de passe de votre choix.</p>
        </div>

        <button type="submit" class="ab-btn-cta w-full py-3.5">Continuer <x-icon name="arrow-right" class="h-4 w-4" stroke="2.4" /></button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="font-bold text-brand-700 hover:text-brand-800">Se connecter</a>
    </p>
</x-guest-layout>
