<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
     */
    public function boot(): void
    {
        $isConfiguredAdministrator = fn ($user): bool => filled(config('store.admin_email'))
            && hash_equals(mb_strtolower((string) config('store.admin_email')), mb_strtolower((string) $user->email));
        $hasRole = fn ($user, array $roles): bool => in_array($user->admin_role, $roles, true);

        Gate::define('manage-store', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'sales_administrator', 'inventory_manager', 'installation_coordinator', 'marketing_manager', 'bi_analyst']));
        Gate::define('view-admin-bi', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'sales_administrator', 'marketing_manager', 'bi_analyst']));
        Gate::define('manage-store-orders', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'sales_administrator']));
        Gate::define('manage-store-products', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'inventory_manager', 'marketing_manager']));
        Gate::define('manage-store-inventory', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'inventory_manager']));
        Gate::define('manage-installations', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'installation_coordinator']));
        Gate::define('manage-store-marketing', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin', 'store_manager', 'marketing_manager']));
        Gate::define('manage-store-users', fn ($user): bool => $isConfiguredAdministrator($user)
            || $hasRole($user, ['super_admin']));
    }
}
