<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BranchService;

class BranchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BranchService::class, function ($app) {
            return new BranchService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}