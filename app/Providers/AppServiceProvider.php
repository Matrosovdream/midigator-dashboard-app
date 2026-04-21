<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(fn ($user) => method_exists($user, 'isPlatformAdmin') && $user->isPlatformAdmin() ? true : null);

        Gate::define('right', fn ($user, string $right) => method_exists($user, 'hasRight') && $user->hasRight($right));
    }
}
