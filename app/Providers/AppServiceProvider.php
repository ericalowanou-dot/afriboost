<?php

namespace App\Providers;

use App\Models\CampaignRequest;
use App\Models\Mission;
use App\Models\Participation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('missionSlug', function (string $value) {
            $mission = Mission::findByUrlSlug($value);

            abort_unless($mission, 404);

            return $mission;
        });

        // Compteurs « à traiter » affichés dans le menu d'administration
        View::composer('layouts.admin', function ($view) {
            $view->with('adminBadges', [
                'queue' => Participation::whereIn('status', [Participation::STATUS_SUBMITTED, Participation::STATUS_UNDER_REVIEW])->count(),
                'creators' => User::where('role', 'creator')->where('verification_status', User::VERIFICATION_PENDING)->where('registration_step', '>=', 2)->count(),
                'payouts' => Transaction::where('type', Transaction::TYPE_PAYOUT)->where('status', Transaction::STATUS_PENDING)->count(),
                'requests' => CampaignRequest::where('status', 'new')->count(),
            ]);
        });
    }
}
