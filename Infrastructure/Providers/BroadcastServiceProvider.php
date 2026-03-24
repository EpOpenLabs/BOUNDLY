<?php

namespace Infrastructure\Providers;

use Domain\Shared\Events\ShouldBroadcastToExterior;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Integrations\WebSockets\BroadcastableDomainEvent;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Check for the basement's broadcasting configuration
        if (! $this->app->configurationIsCached()) {
            $this->app->make('config')->set('broadcasting', require base_path('Infrastructure/LaravelEngine/config/broadcasting.php'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🌉 BRIDGE: Listen for any domain event that should broadcast
        Event::listen(function (ShouldBroadcastToExterior $event) {
            // Wrap the domain event in a serializable Laravel broadcast event
            broadcast(new BroadcastableDomainEvent($event));
        });

        // Register the channels file from the basement
        require base_path('Infrastructure/LaravelEngine/routes/channels.php');
    }
}
