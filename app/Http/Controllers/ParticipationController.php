<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationController extends Controller
{
    /** Onglets de la page « Mes participations » et statuts couverts par chacun. */
    public const TABS = [
        'in_progress' => [Participation::STATUS_IN_PROGRESS],
        'submitted' => [Participation::STATUS_SUBMITTED, Participation::STATUS_UNDER_REVIEW],
        'validated' => [Participation::STATUS_VALIDATED, Participation::STATUS_PAID],
        'rejected' => [Participation::STATUS_REJECTED],
    ];

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = array_key_exists($status, self::TABS) ? $status : '';

        $user = auth()->user()->loadMissing('socialNetworks');

        $all = $user->participations()
            ->with('mission')
            ->latest('updated_at')
            ->get()
            ->each(fn (Participation $participation) => $participation->setRelation('user', $user));

        $counts = collect(self::TABS)->map(
            fn (array $statuses) => $all->whereIn('status', $statuses)->count()
        );

        $participations = $status
            ? $all->whereIn('status', self::TABS[$status])->values()
            : $all;

        return view('creator.participations.index', compact('participations', 'status', 'counts'));
    }
}
