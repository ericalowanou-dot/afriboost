<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Participation;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'creators' => User::where('role', 'creator')->count(),
            'missions' => Mission::count(),
            'published' => Mission::where('status', 'published')->count(),
            'pending' => Participation::whereIn('status', [
                Participation::STATUS_SUBMITTED,
                Participation::STATUS_UNDER_REVIEW,
            ])->count(),
            'validated' => Participation::whereIn('status', [
                Participation::STATUS_VALIDATED,
                Participation::STATUS_PAID,
            ])->count(),
        ];

        $recent = Participation::with(['user', 'mission'])
            ->whereIn('status', [
                Participation::STATUS_SUBMITTED,
                Participation::STATUS_UNDER_REVIEW,
            ])
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent'));
    }
}
