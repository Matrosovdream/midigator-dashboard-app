<?php

namespace App\Providers;

use App\Models\Chargeback;
use App\Models\PreventionAlert;
use App\Models\User;
use App\Observers\ChargebackObserver;
use App\Observers\PreventionObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Model → Observer bindings.
     */
    protected array $observers = [
        Chargeback::class => ChargebackObserver::class,
        PreventionAlert::class => PreventionObserver::class,
        User::class => UserObserver::class,
    ];

    public function boot(): void
    {
        foreach ($this->observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}
