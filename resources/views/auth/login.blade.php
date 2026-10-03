<x-guest-layout title="Connexion — AfriBoost">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold">Connexion</h1>
        <p class="mt-1 text-sm text-slate-500">Accède à tes missions et à ton wallet.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="email" class="ab-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" class="ab-input">
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="ab-label">Mot de passe</label>
                <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-bold text-brand-700 hover:text-brand-800">Oublié ?</a>
            </div>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="ab-input pr-12">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 hover:text-slate-600" :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                    <x-icon name="eye" class="h-[18px] w-[18px]" />
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Se souvenir de moi
        </label>

        <button type="submit" class="ab-btn-cta w-full py-3.5">Se connecter</button>
    </form>

    <div class="mt-6 space-y-3 text-center text-sm">
        <p class="text-slate-500">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-bold text-cta-600 hover:text-cta-700">Créer un compte</a>
        </p>
        <a href="{{ route('missions.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-slate-600">
            Voir les missions sans compte <x-icon name="arrow-right" class="h-3.5 w-3.5" />
        </a>
    </div>
</x-guest-layout>
