<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $statusMap = [
            'in_progress' => Participation::STATUS_IN_PROGRESS,
            'submitted' => [Participation::STATUS_SUBMITTED, Participation::STATUS_UNDER_REVIEW],
            'validated' => [Participation::STATUS_VALIDATED, Participation::STATUS_PAID],
            'rejected' => Participation::STATUS_REJECTED,
        ];

        $query = auth()->user()
            ->participations()
            ->with('mission')
            ->latest();

        if ($status && isset($statusMap[$status])) {
            $filter = $statusMap[$status];
            is_array($filter)
                ? $query->whereIn('status', $filter)
                : $query->where('status', $filter);
        }

        $participations = $query->get();

        return view('creator.participations.index', compact('participations', 'status'));
    }
}
