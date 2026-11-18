<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
        Gate::before(function (User $user): ?bool {
            return $user->hasRole(RoleName::SuperAdmin->value) ? true : null;
        });

        $this->configureModelStrictness();
    }

    /**
     * Fail loudly in tests when a relation is lazily loaded or an unknown attribute is set.
     */
    private function configureModelStrictness(): void
    {
        if (! $this->app->runningUnitTests()) {
            return;
        }

        Model::preventLazyLoading();
        Model::preventSilentlyDiscardingAttributes();
        Model::preventAccessingMissingAttributes();
    }
}
