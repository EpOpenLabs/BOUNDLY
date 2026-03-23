<?php

namespace Infrastructure\FrameworkCore\Testing;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;

/**
 * The base test case for all BOUNDLY domain tests.
 * It overrides Laravel's standard migration logic to run core:migrate
 * automatically between tests, ensuring a fresh and accurate database state.
 */
abstract class BoundlyTestCase extends BaseTestCase
{
    use RefreshDatabase;

    // By default Laravel tests assume standard migrations, 
    // but BOUNDLY uses declarative Entity attributes.
    // We override refreshTestDatabase so RefreshDatabase trait uses our engine.
    protected function refreshTestDatabase()
    {
        if (! $this->app->environment('testing')) {
            throw new \Exception('BoundlyTestCase must only be executed in a testing environment.');
        }

        // Drop all standard tables to start clean
        $this->artisan('migrate:fresh');

        // Execute BOUNDLY's magic migration engine based on attributes
        $this->artisan('core:migrate', ['--lang' => 'en']);

        $this->app[Kernel::class]->setArtisan(null);
    }
}
