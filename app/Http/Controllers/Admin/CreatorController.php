<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialNetwork;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreatorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'verification' => $request->string('verification')->toString() ?: 'all',
            'tier' => $request->string('tier')->toString() ?: 'all',
            'network' => $request->string('network')->toString() ?: 'all',
            'status' => $request->string('status')->toString() ?: 'all',
            'search' => $request->string('search')->toString(),
            'sort' => $request->string('sort')->toString() ?: 'latest',
        ];

        $creators = User::query()
            ->where('role', 'creator')
            ->with('wallet')
            ->withCount('participations')
            ->when($filters['verification'] === 'pending', fn ($q) => $q->where('verification_status', User::VERIFICATION_PENDING))
            ->when($filters['verification'] === 'verified', fn ($q) => $q->where('verification_status', User::VERIFICATION_VERIFIED))
            ->when($filters['verification'] === 'rejected', fn ($q) => $q->where('verification_status', User::VERIFICATION_REJECTED))
            ->when($filters['tier'] === 'top', fn ($q) => $q->where('creator_tier', User::TIER_TOP))
            ->when($filters['tier'] === 'medium', fn ($q) => $q->where('creator_tier', User::TIER_MEDIUM))
            ->when($filters['tier'] === 'basic', fn ($q) => $q->where('creator_tier', User::TIER_BASIC))
            ->when($filters['tier'] === 'none', fn ($q) => $q->whereNull('creator_tier'))
            ->when($filters['network'] !== 'all', fn ($q) => $q->whereHas(
                'socialNetworks',
                fn ($sq) => $sq->where('platform', $filters['network'])
            ))
            ->when($filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $search = '%'.$filters['search'].'%';
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->when($filters['sort'] === 'participations_desc', fn ($q) => $q->orderByDesc('participations_count'))
            ->when($filters['sort'] === 'name_asc', fn ($q) => $q->orderBy('name'))
            ->when($filters['sort'] === 'latest', fn ($q) => $q->latest())
            ->paginate(20)
            ->withQueryString();

        $pendingCount = User::where('role', 'creator')
            ->where('verification_status', User::VERIFICATION_PENDING)
            ->count();

        return view('admin.creators.index', compact('creators', 'filters', 'pendingCount'));
    }

    public function show(User $creator): View
    {
        abort_unless($creator->isCreator(), 404);

        $creator->load(['socialNetworks', 'wallet', 'participations.mission', 'verifier']);

        return view('admin.creators.show', compact('creator'));
    }

    public function updateStatus(Request $request, User $creator)
    {
        abort_unless($creator->isCreator(), 404);

        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,blocked'],
            'status_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $creator->update($data);

        return back()->with('success', 'Statut du créateur mis à jour.');
    }

    public function verify(Request $request, User $creator)
    {
        abort_unless($creator->isCreator(), 404);

        $creator->update([
            'verification_status' => User::VERIFICATION_VERIFIED,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'status_reason' => null,
        ]);

        return back()->with('success', 'Compte créateur vérifié.');
    }

    public function rejectVerification(Request $request, User $creator)
    {
        abort_unless($creator->isCreator(), 404);

        $data = $request->validate([
            'status_reason' => ['required', 'string', 'max:1000'],
        ]);

        $creator->update([
            'verification_status' => User::VERIFICATION_REJECTED,
            'status_reason' => $data['status_reason'],
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Compte créateur refusé.');
    }

    public function updateTier(Request $request, User $creator)
    {
        abort_unless($creator->isCreator(), 404);

        $data = $request->validate([
            'creator_tier' => ['nullable', 'in:top,medium,basic'],
        ]);

        $creator->update(['creator_tier' => $data['creator_tier']]);

        return back()->with('success', 'Classement du créateur mis à jour.');
    }

    public function updateNetwork(Request $request, User $creator, SocialNetwork $network)
    {
        abort_unless($creator->isCreator(), 404);
        abort_unless($network->user_id === $creator->id, 404);

        $data = $request->validate([
            'follower_count' => ['nullable', 'integer', 'min:0'],
            'handle' => ['nullable', 'string', 'max:100'],
        ]);

        $network->update($data);

        return back()->with('success', 'Réseau social mis à jour.');
    }
}
