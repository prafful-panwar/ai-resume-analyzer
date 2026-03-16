<?php

namespace App\Providers;

use App\Repositories\Contracts\JobDescriptionRepositoryInterface;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use App\Repositories\JobDescriptionRepository;
use App\Repositories\ResumeAnalysisRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(JobDescriptionRepositoryInterface::class, JobDescriptionRepository::class);
        $this->app->bind(ResumeAnalysisRepositoryInterface::class, ResumeAnalysisRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
