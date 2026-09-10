@props([
    'viewLinks' => [],
    'tierNetworks' => [],
    'tierAction' => '',
    'verifyAction' => '',
    'rejectAction' => '',
    'verifyLabel' => 'Valider',
    'rejectLabel' => 'Refuser',
    'rejectField' => 'status_reason',
    'rejectPlaceholder' => 'Motif du refus',
    'canDecide' => true,
    'prefix' => 'modal',
])

@php
    $uid = $prefix.'-'.uniqid();
    $singleViewLink = count($viewLinks) === 1 ? ($viewLinks[0]['url'] ?? null) : null;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-1.5']) }}>
    @if ($singleViewLink)
        <a href="{{ $singleViewLink }}" target="_blank" rel="noopener"
           class="inline-flex rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800">
            Voir
        </a>
    @elseif (count($viewLinks) > 0)
        <button type="button"
                x-data
                @click="$dispatch('open-modal', '{{ $uid }}-view')"
                class="inline-flex rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800">
            Voir
        </button>
    @else
        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Voir</span>
    @endif

    @if (count($tierNetworks) > 0)
        <button type="button"
                x-data
                @click="$dispatch('open-modal', '{{ $uid }}-tier')"
                class="inline-flex rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-900">
            Classer
        </button>
    @else
        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Classer</span>
    @endif

    @if ($canDecide)
        <button type="button"
                x-data
                @click="$dispatch('open-modal', '{{ $uid }}-decision')"
                class="inline-flex rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">
            Décider
        </button>
    @endif
</div>

@if (count($viewLinks) > 1)
    <x-admin.modal :name="$uid.'-view'" title="Comptes à consulter">
        <ul class="space-y-3">
            @foreach ($viewLinks as $link)
                <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800">{{ $link['label'] }}</p>
                        @if (! empty($link['meta']))
                            <p class="text-xs text-slate-500">{{ $link['meta'] }}</p>
                        @endif
                        @if (! empty($link['url']))
                            <p class="truncate text-xs text-teal-700">{{ $link['url'] }}</p>
                        @endif
                    </div>
                    @if (! empty($link['url']))
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                           class="shrink-0 rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800">
                            Voir
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-admin.modal>
@endif

@if (count($tierNetworks) > 0)
    <x-admin.modal :name="$uid.'-tier'" title="Classer les comptes">
        <form method="POST" action="{{ $tierAction }}" class="space-y-4">
            @csrf
            @method('PATCH')
            @foreach ($tierNetworks as $network)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 p-3">
                    <div>
                        <p class="font-bold">{{ $network['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $network['handle'] ?? '—' }}</p>
                    </div>
                    <select name="networks[{{ $network['id'] }}][creator_tier]"
                            class="rounded-xl border-slate-200 text-sm">
                        <option value="">Non classé</option>
                        @foreach (['top' => 'Top', 'medium' => 'Medium', 'basic' => 'Basique'] as $value => $label)
                            <option value="{{ $value }}" @selected(($network['tier'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <button type="submit" class="w-full rounded-xl bg-slate-900 py-2.5 text-sm font-bold text-white">
                Enregistrer le classement
            </button>
        </form>
    </x-admin.modal>
@endif

@if ($canDecide)
    <x-admin.modal :name="$uid.'-decision'" title="Valider ou refuser">
        <div class="space-y-4">
            <form method="POST" action="{{ $verifyAction }}">
                @csrf
                <button type="submit"
                        class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white hover:bg-emerald-700">
                    {{ $verifyLabel }}
                </button>
            </form>

            <form method="POST" action="{{ $rejectAction }}" class="space-y-3 border-t border-slate-100 pt-4">
                @csrf
                <label class="block text-sm font-semibold text-slate-700">{{ $rejectPlaceholder }}</label>
                <textarea name="{{ $rejectField }}" rows="3" required
                          class="w-full rounded-xl border-slate-200 text-sm"
                          placeholder="{{ $rejectPlaceholder }}"></textarea>
                <button type="submit"
                        class="w-full rounded-xl bg-rose-600 py-3 text-sm font-bold text-white hover:bg-rose-700">
                    {{ $rejectLabel }}
                </button>
            </form>
        </div>
    </x-admin.modal>
@endif
