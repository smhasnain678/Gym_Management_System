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
        // Fetch settings once per request to prevent N+1 queries.
        // Fallback values are used when the table does not yet exist (fresh install).
        $getSettings = function () {
            static $settings = null;
            static $loaded = false;
            
            if (!$loaded) {
                try {
                    $settings = \App\Models\GymSetting::first();
                } catch (\Exception $e) {
                    $settings = null;
                }
                $loaded = true;
            }
            
            return $settings;
        };

        /**
         * gymTimeFormat() — formats only the time portion using the saved time_format setting.
         * 12h → "04:18 PM"   24h → "16:18"
         */
        \Illuminate\Support\Carbon::macro('gymTimeFormat', function () use ($getSettings) {
            $settings = $getSettings();
            $timeFmt = ($settings && $settings->time_format === '24h') ? 'H:i' : 'h:i A';
            return $this->format($timeFmt);
        });

        /**
         * gymDateFormat() — formats only the date portion using the saved date_format setting.
         * d/m/Y → "24/08/2026"   m/d/Y → "08/24/2026"   Y-m-d → "2026-08-24"
         * Falls back to d/m/Y when the setting is unavailable.
         */
        \Illuminate\Support\Carbon::macro('gymDateFormat', function () use ($getSettings) {
            $settings = $getSettings();
            $dateFmt = ($settings && $settings->date_format) ? $settings->date_format : 'd/m/Y';
            return $this->format($dateFmt);
        });

        /**
         * gymDateTimeFormat() — formats date + time using both saved settings.
         * Does NOT accept a hardcoded pattern — reads date_format and time_format from DB.
         * Example (DD/MM/YYYY + 24h): "24/08/2026 16:18"
         * Example (MM/DD/YYYY + 12h): "08/24/2026 04:18 PM"
         */
        \Illuminate\Support\Carbon::macro('gymDateTimeFormat', function () use ($getSettings) {
            $settings = $getSettings();
            $dateFmt = ($settings && $settings->date_format) ? $settings->date_format : 'd/m/Y';
            $timeFmt = ($settings && $settings->time_format === '24h') ? 'H:i' : 'h:i A';
            return $this->format($dateFmt . ' ' . $timeFmt);
        });

        View::composer('*', function ($view) use ($getSettings) {
            $view->with('gymSettings', $getSettings());
        });
    }
}
