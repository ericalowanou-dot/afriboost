<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Mission;
use App\Models\Participation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'creators' => User::where('role', 'creator')->count(),
            'creators_verified' => User::where('role', 'creator')->where('verification_status', User::VERIFICATION_VERIFIED)->count(),
            'creators_pending' => User::where('role', 'creator')->where('verification_status', User::VERIFICATION_PENDING)->count(),
            'missions' => Mission::count(),
            'published' => Mission::published()->active()->count(),
            'campaigns_active' => Campaign::where('status', 'active')->count(),
            'pending' => Participation::whereIn('status', [
                Participation::STATUS_SUBMITTED,
                Participation::STATUS_UNDER_REVIEW,
            ])->count(),
            'validated' => Participation::whereIn('status', [
                Participation::STATUS_VALIDATED,
                Participation::STATUS_PAID,
            ])->count(),
            'rejected' => Participation::where('status', Participation::STATUS_REJECTED)->count(),
            'rewards' => (float) Transaction::where('type', Transaction::TYPE_REWARD)->where('status', Transaction::STATUS_COMPLETED)->sum('amount_usd'),
            'payouts_pending' => Transaction::where('type', Transaction::TYPE_PAYOUT)->where('status', Transaction::STATUS_PENDING)->count(),
            'payouts_pending_usd' => (float) Transaction::where('type', Transaction::TYPE_PAYOUT)->where('status', Transaction::STATUS_PENDING)->sum('amount_usd'),
        ];

        $reviewed = $stats['validated'] + $stats['rejected'];
        $stats['approval_rate'] = $reviewed > 0 ? round($stats['validated'] / $reviewed * 100) : null;

        // Soumissions des 14 derniers jours, pour le graphique d'activité
        $since = now()->subDays(13)->startOfDay();
        $daily = Participation::whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $since)
            ->get(['submitted_at'])
            ->countBy(fn (Participation $p) => $p->submitted_at->toDateString());

        $activity = collect(range(13, 0))->map(function (int $daysAgo) use ($daily) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('d M'),
                'total' => (int) ($daily[$date->toDateString()] ?? 0),
            ];
        });

        $recent = Participation::with(['user', 'mission'])
            ->whereIn('status', [
                Participation::STATUS_SUBMITTED,
                Participation::STATUS_UNDER_REVIEW,
            ])
            ->oldest('submitted_at')
            ->limit(6)
            ->get();

        $pendingCreators = User::where('role', 'creator')
            ->where('verification_status', User::VERIFICATION_PENDING)
            ->where('registration_step', '>=', 2)
            ->with('socialNetworks')
            ->latest()
            ->limit(5)
            ->get();

        $logs = ActivityLog::with('user')->latest()->limit(6)->get();

        return view('admin.dashboard', compact('stats', 'activity', 'recent', 'pendingCreators', 'logs'));
    }
}
