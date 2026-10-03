<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\CampaignRequestController as AdminCampaignRequestController;
use App\Http\Controllers\Admin\CreatorController as AdminCreatorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MissionController as AdminMissionController;
use App\Http\Controllers\Admin\ParticipationController as AdminParticipationController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\BrandRequestController;
use App\Http\Controllers\CreatorProfileController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('missions.index'));

// Page publique destinée aux marques
Route::get('/lancer-une-campagne', [BrandRequestController::class, 'create'])->name('brands.create');
Route::post('/lancer-une-campagne', [BrandRequestController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('brands.store');

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'missions.index');
})->middleware(['auth', 'active', 'registration.complete'])->name('dashboard');

// Missions publiques — consultation sans compte
Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
Route::get('/missions/{reseau}', [MissionController::class, 'index'])
    ->whereIn('reseau', ['facebook', 'tiktok', 'instagram', 'youtube'])
    ->name('missions.network');
Route::get('/missions/{reseau}/{missionSlug}', [MissionController::class, 'show'])
    ->whereIn('reseau', ['facebook', 'tiktok', 'instagram', 'youtube'])
    ->where('missionSlug', '[A-Za-z0-9\-]+-\d+')
    ->name('missions.show');

Route::middleware(['auth', 'active', 'registration.complete'])->group(function () {
    Route::post('/missions/{reseau}/{missionSlug}/participer', [MissionController::class, 'participate'])
        ->whereIn('reseau', ['facebook', 'tiktok', 'instagram', 'youtube'])
        ->where('missionSlug', '[A-Za-z0-9\-]+-\d+')
        ->name('missions.participate');

    Route::post('/missions/{reseau}/{missionSlug}/soumettre', [MissionController::class, 'submit'])
        ->whereIn('reseau', ['facebook', 'tiktok', 'instagram', 'youtube'])
        ->where('missionSlug', '[A-Za-z0-9\-]+-\d+')
        ->name('missions.submit');

    Route::get('/mes-participations', [ParticipationController::class, 'index'])->name('participations.index');
    Route::get('/mon-wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/mon-wallet/retrait', [WalletController::class, 'requestPayout'])
        ->middleware('throttle:6,1')
        ->name('wallet.payout');

    Route::get('/mon-profil', [CreatorProfileController::class, 'show'])->name('creator.profile');
    Route::patch('/mon-profil', [CreatorProfileController::class, 'update'])->name('creator.profile.update');
    Route::post('/mon-profil/redemander-verification', [CreatorProfileController::class, 'requestVerification'])->name('creator.profile.request-verification');
    Route::post('/mon-profil/reseaux-sociaux', [CreatorProfileController::class, 'storeNetwork'])->name('creator.networks.store');
    Route::delete('/mon-profil/reseaux-sociaux/{network}', [CreatorProfileController::class, 'destroyNetwork'])->name('creator.networks.destroy');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tableau-de-bord', fn () => redirect()->route('admin.dashboard'))->name('home');

        Route::get('/missions', [AdminMissionController::class, 'index'])->name('missions.index');
        Route::get('/missions/creer', [AdminMissionController::class, 'create'])->name('missions.create');
        Route::post('/missions', [AdminMissionController::class, 'store'])->name('missions.store');
        Route::get('/missions/{mission}', [AdminMissionController::class, 'show'])->whereNumber('mission')->name('missions.show');
        Route::get('/missions/{mission}/modifier', [AdminMissionController::class, 'edit'])->name('missions.edit');
        Route::put('/missions/{mission}', [AdminMissionController::class, 'update'])->name('missions.update');
        Route::delete('/missions/{mission}', [AdminMissionController::class, 'destroy'])->name('missions.destroy');

        Route::get('/campagnes', [AdminCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('/campagnes/creer', [AdminCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campagnes', [AdminCampaignController::class, 'store'])->name('campaigns.store');
        Route::get('/campagnes/{campaign}', [AdminCampaignController::class, 'show'])->whereNumber('campaign')->name('campaigns.show');
        Route::get('/campagnes/{campaign}/modifier', [AdminCampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('/campagnes/{campaign}', [AdminCampaignController::class, 'update'])->name('campaigns.update');
        Route::delete('/campagnes/{campaign}', [AdminCampaignController::class, 'destroy'])->name('campaigns.destroy');

        Route::get('/demandes-marques', [AdminCampaignRequestController::class, 'index'])->name('campaign-requests.index');
        Route::patch('/demandes-marques/{campaignRequest}', [AdminCampaignRequestController::class, 'update'])->name('campaign-requests.update');

        Route::get('/paiements', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('/paiements/{payout}/payer', [AdminPayoutController::class, 'complete'])->name('payouts.complete');
        Route::post('/paiements/{payout}/annuler', [AdminPayoutController::class, 'cancel'])->name('payouts.cancel');

        Route::get('/historique', [AdminActivityLogController::class, 'index'])->name('activity.index');

        Route::get('/verifications', [AdminParticipationController::class, 'index'])->name('participations.index');
        Route::get('/verifications/{participation}', [AdminParticipationController::class, 'show'])->name('participations.show');
        Route::post('/verifications/{participation}/valider', [AdminParticipationController::class, 'validateParticipation'])->name('participations.validate');
        Route::post('/verifications/{participation}/refuser', [AdminParticipationController::class, 'reject'])->name('participations.reject');

        Route::get('/createurs', [AdminCreatorController::class, 'index'])->name('creators.index');
        Route::get('/createurs/{creator}', [AdminCreatorController::class, 'show'])->name('creators.show');
        Route::patch('/createurs/{creator}/statut', [AdminCreatorController::class, 'updateStatus'])->name('creators.status');
        Route::post('/createurs/{creator}/verifier', [AdminCreatorController::class, 'verify'])->name('creators.verify');
        Route::post('/createurs/{creator}/refuser', [AdminCreatorController::class, 'rejectVerification'])->name('creators.reject');
        Route::patch('/createurs/{creator}/classement', [AdminCreatorController::class, 'updateTier'])->name('creators.tier');
        Route::patch('/createurs/{creator}/reseaux/classement', [AdminCreatorController::class, 'updateNetworksTier'])->name('creators.networks.tier');
        Route::patch('/createurs/{creator}/reseaux/{network}', [AdminCreatorController::class, 'updateNetwork'])->name('creators.networks.update');
    });
});

require __DIR__.'/auth.php';
