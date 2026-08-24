<?php

namespace App\Providers;

use App\Models\GymSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Shares the gym settings record with every view so the sidebar/header
     * can display the dynamic gym name and logo without each controller
     * needing to pass the data explicitly.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                $gymSettings = GymSetting::first();
            } catch (\Exception $e) {
                // Table may not exist yet during migrations / fresh installs.
                $gymSettings = null;
            }
            $view->with('gymSettings', $gymSettings);
        });
    }
}
