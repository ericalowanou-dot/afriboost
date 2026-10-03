@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('heading', 'Tableau de bord')
@section('actions')
    <a href="{{ route('admin.missions.create') }}" class="ab-btn-cta py-2"><x-icon name="plus" class="h-4 w-4" stroke="2.6" /> <span class="hidden sm:inline">Nouvelle mission</span></a>
@endsection

@php
    use App\Support\Money;
    $max = max(1, $activity->max('total'));
@endphp

@section('content')
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <x-admin.stat label="À vérifier" :value="$stats['pending']" icon="clipboard-check" tone="cta"
                      :href="route('admin.participations.index')" hint="participations en file" />
        <x-admin.stat label="Créateurs" :value="$stats['creators']" icon="users" tone="brand"
                      :href="route('admin.creators.index')" :hint="$stats['creators_verified'].' vérifiés · '.$stats['creators_pending'].' en attente'" />
        <x-admin.stat label="Récompenses versées" :value="Money::usd($stats['rewards'])" icon="dollar" tone="amber"
                      :hint="$stats['validated'].' participations validées'" />
        <x-admin.stat label="Retraits à payer" :value="Money::usd($stats['payouts_pending_usd'])" icon="banknote" tone="sky"
                      :href="route('admin.payouts.index')" :hint="$stats['payouts_pending'].' demande(s)'" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Activité des 14 derniers jours --}}
        <section class="ab-panel p-5 xl:col-span-2">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="font-extrabold">Contenus soumis</h2>
                    <p class="text-sm text-slate-500">14 derniers jours</p>
                </div>
                <div class="flex gap-4 text-sm">
                    <div><p class="text-slate-500">Missions actives</p><p class="text-lg font-extrabold">{{ $stats['published'] }}</p></div>
                    <div><p class="text-slate-500">Campagnes actives</p><p class="text-lg font-extrabold">{{ $stats['campaigns_active'] }}</p></div>
                    <div><p class="text-slate-500">Taux de validation</p><p class="text-lg font-extrabold">{{ $stats['approval_rate'] !== null ? $stats['approval_rate'].' %' : '—' }}</p></div>
                </div>
            </div>
            <div class="mt-6 flex h-40 items-end gap-1.5" role="img" aria-label="Nombre de contenus soumis par jour">
                @foreach ($activity as $day)
                    <div class="group flex h-full flex-1 flex-col items-center justify-end gap-1">
                        <span class="text-[10px] font-bold text-slate-500 opacity-0 transition group-hover:opacity-100">{{ $day['total'] }}</span>
                        <div class="w-full rounded-t-md {{ $day['total'] ? 'bg-brand-500 group-hover:bg-brand-600' : 'bg-slate-100' }}"
                             style="height: {{ $day['total'] ? max(6, $day['total'] / $max * 100) : 4 }}%"
                             title="{{ $day['label'] }} : {{ $day['total'] }}"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-[11px] font-medium text-slate-400">
                <span>{{ $activity->first()['label'] }}</span>
                <span>{{ $activity->last()['label'] }}</span>
            </div>
        </section>

        {{-- Comptes à vérifier --}}
        <section class="ab-panel p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-extrabold">Comptes à vérifier</h2>
                <a href="{{ route('admin.creators.index', ['verification' => 'pending']) }}" class="text-sm font-bold text-brand-700">Tout voir</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($pendingCreators as $creator)
                    <a href="{{ route('admin.creators.show', $creator) }}" class="flex items-center gap-3 py-2.5 hover:bg-slate-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600">{{ mb_strtoupper(mb_substr($creator->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">{{ $creator->name }}</p>
                            <p class="flex items-center gap-1 text-xs text-slate-500">
                                @foreach ($creator->socialNetworks as $n)
                                    <x-network-icon :network="$n->platform" class="h-3.5 w-3.5" />
                                @endforeach
                                <span class="ml-1">inscrit {{ $creator->created_at->diffForHumans() }}</span>
                            </p>
                        </div>
                        <x-icon name="chevron-right" class="h-4 w-4 text-slate-400" />
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Aucun compte en attente.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- File de vérification --}}
        <section class="ab-panel overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-extrabold">File de vérification <span class="text-sm font-semibold text-slate-400">(plus anciennes d'abord)</span></h2>
                <a href="{{ route('admin.participations.index') }}" class="text-sm font-bold text-brand-700">Tout voir</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-y border-slate-100 bg-slate-50">
                        <tr>
                            <th class="ab-th">Créateur</th>
                            <th class="ab-th">Mission</th>
                            <th class="ab-th">Soumise</th>
                            <th class="ab-th"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recent as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="ab-td font-semibold">{{ $item->user->name }}</td>
                                <td class="ab-td">
                                    <span class="flex items-center gap-2">
                                        <x-network-icon :network="$item->effectiveNetwork()" />
                                        {{ $item->mission->brand_name }}
                                    </span>
                                </td>
                                <td class="ab-td text-slate-500">{{ optional($item->submitted_at)->diffForHumans() }}</td>
                                <td class="ab-td text-right">
                                    <a class="ab-btn bg-cta-50 py-1.5 text-cta-700 hover:bg-cta-100" href="{{ route('admin.participations.show', $item) }}">Vérifier</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucune participation en attente. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Journal --}}
        <section class="ab-panel p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-extrabold">Dernières actions</h2>
                <a href="{{ route('admin.activity.index') }}" class="text-sm font-bold text-brand-700">Historique</a>
            </div>
            <ol class="space-y-3">
                @forelse ($logs as $log)
                    <li class="flex gap-3 text-sm">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ str_contains($log->tone(), 'emerald') ? 'bg-brand-500' : (str_contains($log->tone(), 'red') ? 'bg-cta-500' : (str_contains($log->tone(), 'amber') ? 'bg-amber-500' : 'bg-slate-300')) }}"></span>
                        <div class="min-w-0">
                            <p class="text-slate-700">{{ $log->description }}</p>
                            <p class="text-xs text-slate-400">{{ $log->user?->name ?? 'Système' }} · {{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-500">Aucune action enregistrée.</li>
                @endforelse
            </ol>
        </section>
    </div>
@endsection
