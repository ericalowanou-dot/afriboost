<?php

namespace App\Providers;

use App\Models\Mission;
use Illuminate\Support\Facades\Route;
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
    }
}
