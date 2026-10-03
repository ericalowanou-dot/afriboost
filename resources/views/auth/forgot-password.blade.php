<x-guest-layout title="Mot de passe oublié — AfriBoost">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold">Mot de passe oublié</h1>
        <p class="mt-1 text-sm text-slate-500">Indique ton e-mail : nous t'enverrons un lien de réinitialisation.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="ab-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus inputmode="email" class="ab-input">
        </div>
        <button type="submit" class="ab-btn-brand w-full py-3.5">Envoyer le lien</button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-bold text-brand-700 hover:text-brand-800">
            <x-icon name="arrow-left" class="h-4 w-4" /> Retour à la connexion
        </a>
    </p>
</x-guest-layout>
