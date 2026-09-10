@php
    $m = $mission ?? null;
    $selectedNetworks = old('social_networks', $m?->selectedNetworks() ?? ['tiktok']);
    $networkBudgets = old('network_budgets', $m?->network_budgets ?? []);
    foreach ($selectedNetworks as $net) {
        if (! isset($networkBudgets[$net])) {
            $networkBudgets[$net] = ['basic' => '10', 'medium' => '', 'top' => ''];
        }
    }
    $networkLabels = \App\Models\Mission::NETWORK_LABELS;
@endphp

<form method="POST"
      action="{{ $m ? route('admin.missions.update', $m) : route('admin.missions.store') }}"
      enctype="multipart/form-data"
      class="max-w-3xl space-y-6 rounded-2xl bg-white p-6 shadow-sm"
      x-data="{
          networks: @js($selectedNetworks),
          budgets: @js($networkBudgets),
          toggleNetwork(network) {
              if (this.networks.includes(network)) {
                  this.networks = this.networks.filter(n => n !== network);
                  delete this.budgets[network];
              } else {
                  this.networks.push(network);
                  if (!this.budgets[network]) {
                      this.budgets[network] = { basic: '10', medium: '', top: '' };
                  }
              }
          },
          isSelected(network) { return this.networks.includes(network); }
      }">
    @csrf
    @if ($m) @method('PUT') @endif

    {{-- Entreprise --}}
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-400">Entreprise</h3>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold">Nom de l'entreprise *</label>
                <input name="brand_name" value="{{ old('brand_name', $m->brand_name ?? '') }}" required
                       class="w-full rounded-xl border-slate-200 @error('brand_name') border-red-400 @enderror">
                @error('brand_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Campagne</label>
                <select name="campaign_id" class="w-full rounded-xl border-slate-200">
                    <option value="">—</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected(old('campaign_id', $m->campaign_id ?? '') == $campaign->id)>{{ $campaign->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Logo / photo de l'entreprise</label>
            @if ($m?->logoUrl())
                <img src="{{ $m->logoUrl() }}" alt="Logo" class="mb-2 h-16 w-16 rounded-xl object-cover ring-1 ring-slate-200">
            @endif
            <input type="file" name="logo" accept="image/*"
                   class="block w-full text-sm @error('logo') text-red-600 @enderror">
            @error('logo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Titre de la mission *</label>
            <input name="title" value="{{ old('title', $m->title ?? '') }}" required class="w-full rounded-xl border-slate-200">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Description courte</label>
            <input name="short_description" value="{{ old('short_description', $m->short_description ?? '') }}" class="w-full rounded-xl border-slate-200">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Description *</label>
            <textarea name="description" rows="4" required class="w-full rounded-xl border-slate-200">{{ old('description', $m->description ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Consignes</label>
            <textarea name="instructions" rows="4" class="w-full rounded-xl border-slate-200">{{ old('instructions', $m->instructions ?? '') }}</textarea>
        </div>
    </section>

    {{-- Réseaux & budgets --}}
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-400">Réseaux sociaux & budgets</h3>

        <div>
            <label class="mb-2 block text-sm font-semibold">Réseaux concernés *</label>
            <div class="flex flex-wrap gap-3">
                @foreach ($networkLabels as $key => $label)
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                           :class="isSelected('{{ $key }}') ? 'border-teal-600 bg-teal-50 text-teal-800' : 'border-slate-200 bg-white text-slate-600'">
                        <input type="checkbox"
                               name="social_networks[]"
                               value="{{ $key }}"
                               class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                               :checked="isSelected('{{ $key }}')"
                               @change="toggleNetwork('{{ $key }}')">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('social_networks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Type de contenu</label>
            <select name="content_type" class="w-full rounded-xl border-slate-200 sm:max-w-xs">
                @foreach (['video' => 'Vidéo', 'post' => 'Publication', 'story' => 'Story'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('content_type', $m->content_type ?? 'video') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <template x-for="network in networks" :key="network">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="mb-3 text-sm font-bold text-slate-700" x-text="{
                    facebook: 'Facebook', tiktok: 'TikTok', instagram: 'Instagram', youtube: 'YouTube'
                }[network]"></p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Budget Basique (USD) *</label>
                        <input type="number" step="0.01" min="0"
                               :name="'network_budgets[' + network + '][basic]'"
                               x-model="budgets[network].basic"
                               required
                               class="w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Budget Medium (USD)</label>
                        <input type="number" step="0.01" min="0"
                               :name="'network_budgets[' + network + '][medium]'"
                               x-model="budgets[network].medium"
                               placeholder="= Basique"
                               class="w-full rounded-xl border-slate-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Budget Top (USD)</label>
                        <input type="number" step="0.01" min="0"
                               :name="'network_budgets[' + network + '][top]'"
                               x-model="budgets[network].top"
                               placeholder="= Basique"
                               class="w-full rounded-xl border-slate-200">
                    </div>
                </div>
            </div>
        </template>
        @error('network_budgets')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </section>

    {{-- Dates --}}
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-400">Calendrier</h3>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold">Date de début</label>
                <input type="date" name="starts_at" value="{{ old('starts_at', optional($m->starts_at ?? null)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Date de fin de mission</label>
                <p class="mb-1 text-xs text-slate-400">La mission disparaît du site à cette date</p>
                <input type="date" name="ends_at" value="{{ old('ends_at', optional($m->ends_at ?? null)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border-slate-200">
                @error('ends_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold">Durée de conservation du contenu (jours)</label>
                <p class="mb-1 text-xs text-slate-400">Nombre de jours que la vidéo doit rester en ligne sur le profil du créateur avant le paiement</p>
                <input type="number" name="content_retention_days" min="1"
                       value="{{ old('content_retention_days', $m->content_retention_days ?? '') }}"
                       placeholder="Ex : 7, 14, 30"
                       class="w-full rounded-xl border-slate-200">
                @error('content_retention_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Durée minimale vidéo (secondes)</label>
                <input type="number" name="min_duration_seconds" min="1"
                       value="{{ old('min_duration_seconds', $m->min_duration_seconds ?? '') }}"
                       class="w-full rounded-xl border-slate-200">
            </div>
        </div>
    </section>

    {{-- Exemple de contenu --}}
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-400">Exemple de contenu (optionnel)</h3>

        @if ($m?->contentExampleUrl())
            <div class="flex items-start gap-3">
                @if (str_ends_with(strtolower($m->content_example_path ?? ''), '.mp4') || str_ends_with(strtolower($m->content_example_path ?? ''), '.mov') || str_ends_with(strtolower($m->content_example_path ?? ''), '.webm'))
                    <video src="{{ $m->contentExampleUrl() }}" controls class="max-h-40 rounded-xl"></video>
                @else
                    <img src="{{ $m->contentExampleUrl() }}" alt="Exemple" class="max-h-40 rounded-xl object-cover ring-1 ring-slate-200">
                @endif
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remove_content_example" value="1" class="rounded border-slate-300">
                    Supprimer l'exemple actuel
                </label>
            </div>
        @endif

        <input type="file" name="content_example" accept="image/*,video/mp4,video/webm,video/quicktime"
               class="block w-full text-sm">
        <p class="text-xs text-slate-400">Image ou vidéo (max 20 Mo) montrant le type de contenu attendu.</p>
        @error('content_example')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </section>

    {{-- Statut --}}
    <div>
        <label class="mb-1 block text-sm font-semibold">Statut</label>
        <select name="status" class="w-full rounded-xl border-slate-200 sm:max-w-xs">
            @foreach (['draft' => 'Brouillon', 'published' => 'Publiée', 'closed' => 'Fermée'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $m->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="rounded-xl bg-rose-600 px-5 py-2.5 font-bold text-white">Enregistrer</button>
        <a href="{{ route('admin.missions.index') }}" class="rounded-xl bg-slate-100 px-5 py-2.5 font-semibold text-slate-600">Annuler</a>
    </div>
</form>
