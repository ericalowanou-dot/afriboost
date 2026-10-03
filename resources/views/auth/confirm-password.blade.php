<x-guest-layout title="Confirmer le mot de passe — AfriBoost">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold">Zone sécurisée</h1>
        <p class="mt-1 text-sm text-slate-500">Confirme ton mot de passe pour continuer.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <div>
            <label for="password" class="ab-label">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="ab-input">
        </div>
        <button type="submit" class="ab-btn-brand w-full py-3.5">Confirmer</button>
    </form>
</x-guest-layout>
