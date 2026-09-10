<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Étape 1 sur 2</p>
        <h1 class="mt-1 text-xl font-extrabold text-slate-900">Créer mon compte</h1>
        <p class="mt-1 text-sm text-slate-500">Rejoins AfriBoost et commence à gagner en USD.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
        </div>

        <div>
            <label for="phone" class="mb-1 block text-sm font-semibold text-slate-700">Numéro WhatsApp</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                   placeholder="+228 90 00 00 00"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
            <p class="mt-1 text-xs text-slate-400">Choisissez le mot de passe de votre choix.</p>
        </div>

        <button type="submit" class="w-full rounded-2xl bg-rose-600 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-rose-200">
            Continuer →
        </button>
    </form>

    <div class="mt-6 text-center text-sm">
        <p class="text-slate-500">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-semibold text-teal-700 hover:text-teal-800">Se connecter</a>
        </p>
        <p class="mt-2">
            <a href="{{ route('missions.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600">
                Voir les missions sans compte →
            </a>
        </p>
    </div>
</x-guest-layout>
