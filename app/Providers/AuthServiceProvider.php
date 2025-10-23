<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Employee::class => \App\Policies\EmployeePolicy::class,
        // \App\Models\Branch::class => \App\Policies\BranchPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Master = super-admin: pasa todo
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isMaster') && $user->isMaster()) {
                return true;
            }
        });

        // Gate que usan tus rutas: permite company (y master ya pasa por before)
        Gate::define('access-branches', function ($user) {
            return method_exists($user, 'isCompany') && $user->isCompany();
        });
    }
}
