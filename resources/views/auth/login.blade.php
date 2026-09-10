<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-extrabold text-slate-900">Connexion</h1>
        <p class="mt-1 text-sm text-slate-500">Accédez à vos missions et à votre wallet.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
            <label for="remember_me" class="ms-2 text-sm text-slate-600">Se souvenir de moi</label>
        </div>

        <button type="submit" class="w-full rounded-2xl bg-teal-700 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-teal-200">
            Se connecter
        </button>
    </form>

    <div class="mt-6 space-y-2 text-center text-sm">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="font-semibold text-teal-700 hover:text-teal-800">
                Mot de passe oublié ?
            </a>
        @endif
        <p class="text-slate-500">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-semibold text-rose-600 hover:text-rose-700">Créer un compte</a>
        </p>
        <p>
            <a href="{{ route('missions.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600">
                Voir les missions sans compte →
            </a>
        </p>
    </div>
</x-guest-layout>
