<x-guest-layout title="Nouveau mot de passe — AfriBoost">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold">Nouveau mot de passe</h1>
        <p class="mt-1 text-sm text-slate-500">Choisis un nouveau mot de passe pour ton compte.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="ab-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="ab-input">
        </div>
        <div>
            <label for="password" class="ab-label">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="ab-input">
        </div>
        <div>
            <label for="password_confirmation" class="ab-label">Confirmation</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="ab-input">
        </div>

        <button type="submit" class="ab-btn-brand w-full py-3.5">Réinitialiser le mot de passe</button>
    </form>
</x-guest-layout>
