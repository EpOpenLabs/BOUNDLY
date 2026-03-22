<?php

namespace Infrastructure\Providers;

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

    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Domain\Users\Events\UserCreated::class,
            \Infrastructure\Listeners\WelcomeNewUser::class
        );
    }
}
