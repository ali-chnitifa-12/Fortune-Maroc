<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->environment('local')) {
            URL::forceScheme('https');
        }

        Gate::define('view-dashboard', function ($user) {
            return $user->canViewDashboard();
        });

        Gate::define('view-machine-status', function ($user) {
            return $user->canViewMachineStatus();
        });

        Gate::define('view-line-kpi-board', function ($user) {
            return $user->canViewLineKpiBoard();
        });

        Gate::define('manage-production-plans', function ($user) {
            return $user->canManageProductionPlans();
        });

        Gate::define('approve-production-entries', function ($user) {
            return $user->canApproveProductionEntries();
        });

        Gate::define('view-master-data', function ($user) {
            return $user->canViewMasterData();
        });

        Gate::define('manage-master-data', function ($user) {
            return $user->canManageMasterData();
        });

        Gate::define('manage-users', function ($user) {
            return $user->canManageUsers();
        });
    }
}
