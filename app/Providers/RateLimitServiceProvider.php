<?php

namespace App\Providers;

use App\Jobs\AnalyzeResumeJob;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RateLimiter::for('resume-analysis', function (AnalyzeResumeJob $job) {
            // Allow 2 resume analysis jobs per minute to avoid overwhelming the AI service
            return Limit::perMinute(2);
        });
    }
}
