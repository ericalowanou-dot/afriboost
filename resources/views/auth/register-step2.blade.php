<x-guest-layout title="Connecte ton réseau — AfriBoost">
    <div class="mb-6">
        <div class="flex items-center gap-2">
            <span class="h-1.5 flex-1 rounded-full bg-brand-600"></span>
            <span class="h-1.5 flex-1 rounded-full bg-brand-600"></span>
        </div>
        <p class="mt-3 text-xs font-bold uppercase tracking-wider text-brand-700">Étape 2 sur 2</p>
        <h1 class="mt-1 text-xl font-extrabold">Connecte ton réseau social</h1>
        <p class="mt-1 text-sm text-slate-500">Colle le lien de ton profil TikTok, Instagram, Facebook ou YouTube.</p>
    </div>

    <form method="POST" action="{{ route('register.step2.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="profile_url" class="ab-label">Lien de ton profil</label>
            <div class="relative">
                <input id="profile_url" type="url" name="profile_url" value="{{ old('profile_url') }}" required inputmode="url"
                       placeholder="https://www.tiktok.com/@tonprofil" class="ab-input pr-11">
                <x-icon name="link" class="pointer-events-none absolute right-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-400" />
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><x-network-icon network="tiktok" /> tiktok.com/@pseudo</span>
                <span class="flex items-center gap-1.5"><x-network-icon network="instagram" /> instagram.com/pseudo</span>
                <span class="flex items-center gap-1.5"><x-network-icon network="facebook" /> facebook.com/pseudo</span>
                <span class="flex items-center gap-1.5"><x-network-icon network="youtube" /> youtube.com/@chaine</span>
            </div>
        </div>

        <button type="submit" class="ab-btn-brand w-full py-3.5">Terminer l'inscription</button>
    </form>

    <p class="mt-4 flex items-start gap-2 rounded-2xl bg-slate-50 p-3 text-xs text-slate-500">
        <x-icon name="shield" class="h-4 w-4 text-brand-600" />
        Ton compte sera vérifié par l'équipe AfriBoost. Tu pourras ajouter d'autres réseaux depuis ton profil.
    </p>
</x-guest-layout>
