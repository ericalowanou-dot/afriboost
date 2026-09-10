<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Étape 2 sur 2</p>
        <h1 class="mt-1 text-xl font-extrabold text-slate-900">Connecte ton réseau social</h1>
        <p class="mt-1 text-sm text-slate-500">Colle le lien de ton profil TikTok, Instagram, Facebook ou YouTube.</p>
    </div>

    <form method="POST" action="{{ route('register.step2.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="profile_url" class="mb-1 block text-sm font-semibold text-slate-700">Lien de ton profil</label>
            <input id="profile_url" type="url" name="profile_url" value="{{ old('profile_url') }}" required
                   placeholder="https://www.tiktok.com/@tonprofil"
                   class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-teal-500 focus:ring-teal-500">
            <p class="mt-2 text-xs text-slate-400">
                Exemples : tiktok.com, instagram.com, facebook.com, youtube.com
            </p>
        </div>

        <button type="submit" class="w-full rounded-2xl bg-teal-700 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-teal-200">
            Terminer l'inscription
        </button>
    </form>

    <p class="mt-4 text-center text-xs text-slate-400">
        Ton compte sera vérifié par l'équipe AfriBoost avant validation complète.
    </p>
</x-guest-layout>
