<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Maximum 5 transfer requests per minute per authenticated user.

        RateLimiter::for('transfers', function (Request $request) {

            return Limit::perMinute(5)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

        // Maximum 5 bill payment requests per minute per authenticated user.

        RateLimiter::for('bill-payments', function (Request $request) {

            return Limit::perMinute(5)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });

    }
}
