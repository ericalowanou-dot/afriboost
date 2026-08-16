@extends('layouts.admin')

@section('title', 'Missions')
@section('heading', 'Missions')

@section('content')
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.missions.create') }}" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white">Nouvelle mission</a>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3">Marque</th>
                    <th class="px-4 py-3">Réseau</th>
                    <th class="px-4 py-3">Récompense</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($missions as $mission)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $mission->brand_name }}</div>
                            <div class="text-slate-500">{{ $mission->title }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $mission->networkLabel() }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($mission->reward_usd, 2) }} USD</td>
                        <td class="px-4 py-3">{{ $mission->status }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.missions.edit', $mission) }}" class="font-semibold text-teal-700">Modifier</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $missions->links() }}</div>
@endsection
