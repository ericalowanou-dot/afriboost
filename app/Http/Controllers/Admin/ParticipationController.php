<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participation;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'queue';

        $query = Participation::with(['user', 'mission'])->latest('submitted_at');

        if ($status === 'queue') {
            $query->whereIn('status', [
                Participation::STATUS_SUBMITTED,
                Participation::STATUS_UNDER_REVIEW,
            ]);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $participations = $query->paginate(20)->withQueryString();

        return view('admin.participations.index', compact('participations', 'status'));
    }

    public function show(Participation $participation): View
    {
        $participation->load(['user.socialNetworks', 'mission', 'reviewer']);

        return view('admin.participations.show', compact('participation'));
    }

    public function validateParticipation(Participation $participation)
    {
        $this->wallets->validateParticipation($participation, auth()->user());

        return redirect()
            ->route('admin.participations.index')
            ->with('success', 'Participation validée. Récompense créditée.');
    }

    public function reject(Request $request, Participation $participation)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->wallets->rejectParticipation(
            $participation,
            auth()->user(),
            $data['rejection_reason']
        );

        return redirect()
            ->route('admin.participations.index')
            ->with('success', 'Participation refusée.');
    }
}
