<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\System\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\POS\CashierSession::class => \App\Policies\CashierSessionPolicy::class,
        \App\Models\POS\SalesTransaction::class => \App\Policies\SalesTransactionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate berbasis permission string: {module}.{resource}.{action}
         *
         * Cara pakai di controller:
         *   $this->authorize('pos.transaction.create');
         *   $this->authorize('master-data.supplier.view');
         *
         * Superadmin bypass semua gate.
         */
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
            if ($user->hasPermission($ability)) {
                return true;
            }
        });
    }
}
