@props([
    'networks' => [],
    'action' => '',
    'globalTier' => null,
    'globalAction' => '',
    'creatorId' => null,
    'prefix' => 'tier',
])

@php
    $uid = $prefix.'-'.uniqid();
    $count = count($networks);
    $tierOptions = ['' => 'Non classé', 'top' => 'Top', 'medium' => 'Medium', 'basic' => 'Basique'];
@endphp

<div
    x-data="{
        saving: false,
        feedback: '',
        modalOpen: false,
        draft: @js(collect($networks)->mapWithKeys(fn ($n) => [$n['id'] => $n['tier'] ?? ''])->all()),
        async saveNetworks(payload) {
            this.saving = true;
            this.feedback = '';
            try {
                const res = await fetch(@js($action), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ networks: payload }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Erreur lors de l\'enregistrement.');
                this.feedback = data.message;
                window.dispatchEvent(new CustomEvent('tier-updated', { detail: data }));
                return true;
            } catch (e) {
                this.feedback = e.message;
                return false;
            } finally {
                this.saving = false;
            }
        },
        async saveGlobal(tier) {
            if (!@js($globalAction)) return;
            this.saving = true;
            this.feedback = '';
            try {
                const res = await fetch(@js($globalAction), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ creator_tier: tier || null }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Erreur lors de l\'enregistrement.');
                this.feedback = data.message;
                window.dispatchEvent(new CustomEvent('tier-updated', { detail: data }));
            } catch (e) {
                this.feedback = e.message;
            } finally {
                this.saving = false;
            }
        },
        saveSingle(networkId, tier) {
            this.saveNetworks({ [networkId]: { creator_tier: tier || null } });
        },
        async saveAllFromModal() {
            const payload = {};
            for (const [id, tier] of Object.entries(this.draft)) {
                payload[id] = { creator_tier: tier || null };
            }
            const ok = await this.saveNetworks(payload);
            if (ok) this.modalOpen = false;
        },
    }"
    class="inline-flex flex-col gap-0.5"
>
    @if ($count === 1)
        @php $network = $networks[0]; @endphp
        <select
            @change="saveSingle({{ $network['id'] }}, $event.target.value)"
            :disabled="saving"
            class="min-w-[6.5rem] rounded-lg border-slate-200 bg-white py-1.5 pl-2 pr-7 text-xs font-semibold text-slate-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
        >
            @foreach ($tierOptions as $value => $label)
                <option value="{{ $value }}" @selected(($network['tier'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @elseif ($count > 1)
        <button type="button"
                @click="modalOpen = true"
                :disabled="saving"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            Classer
            <span class="rounded-full bg-slate-100 px-1.5 text-[10px]">{{ $count }}</span>
            <span class="text-slate-400">▾</span>
        </button>

        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="modalOpen"
                 x-transition.opacity
                 class="absolute inset-0 bg-slate-900/50"
                 @click="modalOpen = false"></div>
            <div x-show="modalOpen"
                 x-transition
                 class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl"
                 @click.stop>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold text-slate-900">Classer les comptes</h3>
                    <button type="button" @click="modalOpen = false"
                            class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">✕</button>
                </div>
                <div class="space-y-3">
                    @foreach ($networks as $network)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <div>
                                <p class="font-bold text-slate-800">{{ $network['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $network['handle'] ?? '—' }}</p>
                            </div>
                            <select x-model="draft[{{ $network['id'] }}]"
                                    class="rounded-xl border-slate-200 text-sm">
                                @foreach ($tierOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <button type="button"
                            @click="saveAllFromModal()"
                            :disabled="saving"
                            class="w-full rounded-xl bg-slate-900 py-2.5 text-sm font-bold text-white disabled:opacity-60">
                        <span x-show="!saving">Enregistrer le classement</span>
                        <span x-show="saving" x-cloak>Enregistrement…</span>
                    </button>
                </div>
            </div>
        </div>
    @else
        <select
            @change="saveGlobal($event.target.value)"
            :disabled="saving"
            class="min-w-[6.5rem] rounded-lg border-slate-200 bg-white py-1.5 pl-2 pr-7 text-xs font-semibold text-slate-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
        >
            @foreach ($tierOptions as $value => $label)
                <option value="{{ $value }}" @selected($globalTier === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @endif

    <span x-show="feedback" x-text="feedback" x-cloak
          class="max-w-[9rem] truncate text-[10px] font-medium text-emerald-600"></span>
</div>
