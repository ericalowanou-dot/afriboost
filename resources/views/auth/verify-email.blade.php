<x-guest-layout title="Vérifie ton e-mail — AfriBoost">
    <div class="mb-6">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-icon name="mail" class="h-6 w-6" /></span>
        <h1 class="mt-3 text-xl font-extrabold">Vérifie ton adresse e-mail</h1>
        <p class="mt-1 text-sm text-slate-500">Clique sur le lien que nous venons de t'envoyer. Tu ne l'as pas reçu ? Nous pouvons t'en envoyer un autre.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-2xl bg-brand-50 px-4 py-3 text-sm font-semibold text-brand-800">
            Un nouveau lien de vérification a été envoyé à ton adresse e-mail.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="ab-btn-brand w-full py-3.5">Renvoyer l'e-mail</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ab-btn-ghost w-full">Se déconnecter</button>
        </form>
    </div>
</x-guest-layout>
