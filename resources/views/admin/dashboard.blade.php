@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('heading', 'Tableau de bord')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            'Créateurs' => $stats['creators'],
            'Comptes à vérifier' => $stats['creators_pending'],
            'Missions' => $stats['missions'],
            'Publiées' => $stats['published'],
            'Participations à vérifier' => $stats['pending'],
            'Validées' => $stats['validated'],
        ] as $label => $value)
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-extrabold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold">Nouveaux créateurs à vérifier</h2>
            <a href="{{ route('admin.creators.index', ['filter' => 'pending']) }}" class="text-sm font-semibold text-teal-700">Tout voir</a>
        </div>
        @if ($stats['creators_pending'] > 0)
            <p class="text-sm text-amber-700">{{ $stats['creators_pending'] }} compte(s) en attente de vérification.</p>
        @else
            <p class="text-sm text-slate-500">Aucun nouveau compte en attente.</p>
        @endif
    </section>

    <section class="mt-8 rounded-2xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold">File de vérification</h2>
            <a href="{{ route('admin.participations.index') }}" class="text-sm font-semibold text-teal-700">Tout voir</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3 pr-4">Créateur</th>
                        <th class="pb-3 pr-4">Mission</th>
                        <th class="pb-3 pr-4">Soumise</th>
                        <th class="pb-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $item)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 pr-4 font-medium">{{ $item->user->name }}</td>
                            <td class="py-3 pr-4">{{ $item->mission->brand_name }}</td>
                            <td class="py-3 pr-4">{{ optional($item->submitted_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-3"><a class="font-semibold text-rose-600" href="{{ route('admin.participations.show', $item) }}">Vérifier</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-slate-500">Aucune participation en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
