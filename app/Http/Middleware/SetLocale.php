<?php

namespace App\Http\Middleware;

use App\Models\GymSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the gym's configured language as the application locale.
     *
     * The language is stored in the gym_settings table. If no setting exists
     * or the user is a guest, the default application locale is used.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            try {
                $settings = GymSetting::first();
                if ($settings && $settings->language) {
                    App::setLocale($settings->language);
                }
            } catch (\Throwable) {
                // If DB is not available (e.g., during tests without DB), use default
            }
        }

        return $next($request);
    }
}
