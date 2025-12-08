<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
     */
    public function boot(): void
    {
        // Define Gates for menu authorization
        Gate::define('manage-users', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-roles', function ($user) {
            return $user->isMasterAdmin();
        });

        Gate::define('manage-profiles', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
    }
}
