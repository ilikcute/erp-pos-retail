<?php

namespace App\Providers;

use App\Models\POS\CashierSession;
use App\Models\POS\SalesTransaction;
use App\Models\System\User;
use App\Policies\CashierSessionPolicy;
use App\Policies\SalesTransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        CashierSession::class => CashierSessionPolicy::class,
        SalesTransaction::class => SalesTransactionPolicy::class,
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
