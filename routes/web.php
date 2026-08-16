<?php

use App\Http\Controllers\Admin\CreatorController as AdminCreatorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MissionController as AdminMissionController;
use App\Http\Controllers\Admin\ParticipationController as AdminParticipationController;
use App\Http\Controllers\CreatorProfileController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'missions.index')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'missions.index');
})->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    // Créateur — missions (style slug descriptif)
    Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
    Route::get('/missions/{reseau}', [MissionController::class, 'index'])
        ->whereIn('reseau', ['facebook', 'tiktok', 'instagram', 'youtube'])
        ->name('missions.network');

    Route::get('/missions/{reseau}/{missionSlug}', [MissionController::class, 'show'])
        ->whereIn('reseau', ['facebook', 'tiktok', 'instagram', 'youtube'])
        ->where('missionSlug', '[A-Za-z0-9\-]+-\d+')
        ->name('missions.show');

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

    Route::get('/mon-profil', [CreatorProfileController::class, 'show'])->name('creator.profile');
    Route::patch('/mon-profil', [CreatorProfileController::class, 'update'])->name('creator.profile.update');
    Route::post('/mon-profil/reseaux-sociaux', [CreatorProfileController::class, 'storeNetwork'])->name('creator.networks.store');
    Route::delete('/mon-profil/reseaux-sociaux/{network}', [CreatorProfileController::class, 'destroyNetwork'])->name('creator.networks.destroy');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tableau-de-bord', fn () => redirect()->route('admin.dashboard'))->name('home');

        Route::get('/missions', [AdminMissionController::class, 'index'])->name('missions.index');
        Route::get('/missions/creer', [AdminMissionController::class, 'create'])->name('missions.create');
        Route::post('/missions', [AdminMissionController::class, 'store'])->name('missions.store');
        Route::get('/missions/{mission}/modifier', [AdminMissionController::class, 'edit'])->name('missions.edit');
        Route::put('/missions/{mission}', [AdminMissionController::class, 'update'])->name('missions.update');
        Route::delete('/missions/{mission}', [AdminMissionController::class, 'destroy'])->name('missions.destroy');

        Route::get('/verifications', [AdminParticipationController::class, 'index'])->name('participations.index');
        Route::get('/verifications/{participation}', [AdminParticipationController::class, 'show'])->name('participations.show');
        Route::post('/verifications/{participation}/valider', [AdminParticipationController::class, 'validateParticipation'])->name('participations.validate');
        Route::post('/verifications/{participation}/refuser', [AdminParticipationController::class, 'reject'])->name('participations.reject');

        Route::get('/createurs', [AdminCreatorController::class, 'index'])->name('creators.index');
        Route::patch('/createurs/{creator}/statut', [AdminCreatorController::class, 'updateStatus'])->name('creators.status');
    });
});

require __DIR__.'/auth.php';
