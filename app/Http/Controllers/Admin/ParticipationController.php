<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Participation;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ParticipationController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'queue';
        $missionId = $request->integer('mission_id') ?: null;
        $search = $request->string('search')->toString();

        $query = Participation::with(['user.socialNetworks', 'mission'])->latest('submitted_at');

        if ($status === 'queue') {
            $query->whereIn('status', [
                Participation::STATUS_SUBMITTED,
                Participation::STATUS_UNDER_REVIEW,
            ]);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $query
            ->when($missionId, fn ($q) => $q->where('mission_id', $missionId))
            ->when($search, fn ($q) => $q->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }));

        $participations = $query->paginate(20)->withQueryString();

        $queueCount = Participation::whereIn('status', [
            Participation::STATUS_SUBMITTED,
            Participation::STATUS_UNDER_REVIEW,
        ])->count();

        $missions = Mission::orderBy('brand_name')->get(['id', 'brand_name', 'title']);

        return view('admin.participations.index', compact('participations', 'status', 'queueCount', 'missions', 'missionId', 'search'));
    }

    public function show(Participation $participation): View
    {
        // Ouvrir une participation soumise la fait passer « en vérification »
        if ($participation->status === Participation::STATUS_SUBMITTED) {
            $participation->update(['status' => Participation::STATUS_UNDER_REVIEW]);
        }

        $participation->load(['user.socialNetworks', 'user.participations', 'mission', 'reviewer']);

        return view('admin.participations.show', compact('participation'));
    }

    public function validateParticipation(Participation $participation)
    {
        try {
            $this->wallets->validateParticipation($participation, auth()->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['participation' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.participations.index')
            ->with('success', 'Participation validée. Récompense créditée.');
    }

    public function reject(Request $request, Participation $participation)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->wallets->rejectParticipation($participation, auth()->user(), $data['rejection_reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['participation' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.participations.index')
            ->with('success', 'Participation refusée.');
    }
}
