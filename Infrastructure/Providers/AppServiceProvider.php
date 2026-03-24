<?php

namespace Infrastructure\Providers;

use Domain\Users\Events\UserCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Listeners\WelcomeNewUser;

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
        Event::listen(
            UserCreated::class,
            WelcomeNewUser::class
        );
    }
}
