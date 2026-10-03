<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public const FAMILIES = [
        'participation' => 'Participations',
        'creator' => 'Créateurs',
        'payout' => 'Paiements',
        'mission' => 'Missions',
        'campaign' => 'Campagnes',
    ];

    public function index(Request $request): View
    {
        $family = $request->string('type')->toString();
        $family = array_key_exists($family, self::FAMILIES) ? $family : '';

        $logs = ActivityLog::query()
            ->with('user')
            ->when($family, fn ($q) => $q->where('action', 'like', $family.'.%'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'family' => $family,
            'families' => self::FAMILIES,
        ]);
    }
}
