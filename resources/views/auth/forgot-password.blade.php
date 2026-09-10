<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-extrabold text-slate-900">Mot de passe oublié</h1>
        <p class="mt-1 text-sm text-slate-500">Indiquez votre email et nous vous enverrons un lien de réinitialisation.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
        </div>

        <button type="submit" class="w-full rounded-2xl bg-teal-700 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-teal-200">
            Envoyer le lien
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-semibold text-teal-700 hover:text-teal-800">← Retour à la connexion</a>
    </p>
</x-guest-layout>
