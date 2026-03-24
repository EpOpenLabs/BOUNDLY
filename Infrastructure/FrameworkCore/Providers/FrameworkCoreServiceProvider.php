<?php

namespace Infrastructure\FrameworkCore\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Infrastructure\FrameworkCore\Console\Commands\CoreCacheCommand;
use Infrastructure\FrameworkCore\Console\Commands\CoreDocCommand;
use Infrastructure\FrameworkCore\Console\Commands\CoreMakeEntityCommand;
use Infrastructure\FrameworkCore\Console\Commands\CoreMakeTestCommand;
use Infrastructure\FrameworkCore\Console\Commands\CoreMigrateCommand;
use Infrastructure\FrameworkCore\Console\Commands\CoreWatchCommand;
use Infrastructure\FrameworkCore\Http\Controllers\GenericApiController;
use Infrastructure\FrameworkCore\Http\Middleware\CorsMiddleware;
use Infrastructure\FrameworkCore\Http\Middleware\RateLimitMiddleware;
use Infrastructure\FrameworkCore\Http\Middleware\RequestSizeLimitMiddleware;
use Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize;
use Infrastructure\FrameworkCore\Http\Middleware\SecurityHeadersMiddleware;
use Infrastructure\FrameworkCore\Registry\ActionRegistry;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Services\BruteForceProtectionService;
use Infrastructure\FrameworkCore\Services\InputSanitizer;
use Infrastructure\FrameworkCore\Services\SecurityLogger;
use Infrastructure\FrameworkCore\Validation\EntityValidator;
use Symfony\Component\Finder\Finder;

class FrameworkCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Merge default configuration
        $this->mergeConfigFrom(__DIR__.'/../Config/boundly.php', 'boundly');

        // 2. Register Singleton Registries
        $this->app->singleton(EntityRegistry::class, fn () => new EntityRegistry);
        $this->app->singleton(ActionRegistry::class, fn () => new ActionRegistry);

        // 3. Register EntityValidator as a singleton
        $this->app->singleton(EntityValidator::class, fn ($app) => new EntityValidator($app->make(EntityRegistry::class)));

        // 4. Register SecurityLogger as a singleton
        $this->app->singleton(SecurityLogger::class, fn () => new SecurityLogger);

        // 5. Register BruteForceProtectionService as a singleton
        $this->app->singleton(BruteForceProtectionService::class, function ($app) {
            return new BruteForceProtectionService(
                $app->make('cache')->driver(),
                $app->make(SecurityLogger::class)
            );
        });

        // 6. Register InputSanitizer as a singleton
        $this->app->singleton(InputSanitizer::class, fn () => new InputSanitizer);
    }

    public function boot(): void
    {
        // 4. Set Global Locale
        app()->setLocale(config('boundly.locale', 'en'));

        // 5. Load Translations
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');

        // 6. Use cache in production, scan in development
        if ($this->shouldUseCache()) {
            $this->loadFromCache();
        } else {
            $this->scanEntities();
            $this->scanActions();
        }

        // 7. Register Generic API Routes (with authorization middleware)
        $this->registerGenericRoutes();

        // 8. Register CLI Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CoreMigrateCommand::class,
                CoreWatchCommand::class,
                CoreCacheCommand::class,
                CoreDocCommand::class,
                CoreMakeTestCommand::class,
                CoreMakeEntityCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../Config/boundly.php' => config_path('boundly.php'),
            ], 'boundly-config');
        }
    }

    // -------------------------------------------------------------------------
    // Cache Management
    // -------------------------------------------------------------------------

    protected function shouldUseCache(): bool
    {
        $cachePath = CoreCacheCommand::getCachePath();

        return file_exists($cachePath) && ! config('boundly.disable_cache', false);
    }

    protected function loadFromCache(): void
    {
        $cachePath = CoreCacheCommand::getCachePath();
        $data = require $cachePath;

        $entityRegistry = $this->app->make(EntityRegistry::class);
        $actionRegistry = $this->app->make(ActionRegistry::class);

        // Hydrate registries from the flat cache array
        $entityRegistry->hydrateFromCache($data['entities'] ?? []);
        $actionRegistry->hydrateFromCache($data['actions'] ?? []);
    }

    // -------------------------------------------------------------------------
    // Discovery (Development mode)
    // -------------------------------------------------------------------------

    protected function scanEntities(): void
    {
        $registry = $this->app->make(EntityRegistry::class);
        $srcPath = config('boundly.paths.domain', base_path('Domain'));

        $this->scanDirectory($srcPath, fn (string $class) => $registry->registerClass($class));
    }

    protected function scanActions(): void
    {
        $registry = $this->app->make(ActionRegistry::class);
        $srcPath = config('boundly.paths.application', base_path('Application'));

        $this->scanDirectory($srcPath, fn (string $class) => $registry->registerClass($class));
    }

    protected function scanDirectory(string $path, callable $callback): void
    {
        if (! is_dir($path)) {
            return;
        }

        $finder = new Finder;
        $finder->files()->in($path)->name('*.php');

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());

            if (preg_match('/namespace\s+([^;]+);/', $content, $ns) &&
                preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $cls)) {
                $callback($ns[1].'\\'.$cls[1]);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Routes
    // -------------------------------------------------------------------------

    protected function registerGenericRoutes(): void
    {
        $prefix = config('boundly.api_prefix', 'api');

        $middleware = ['api'];

        if (config('boundly.security.enabled', true)) {
            $middleware[] = SecurityHeadersMiddleware::class;
            $middleware[] = RequestSizeLimitMiddleware::class;
        }

        if (config('boundly.cors.enabled', false)) {
            $middleware[] = CorsMiddleware::class;
        }

        if (config('boundly.rate_limit.enabled', true)) {
            $middleware[] = RateLimitMiddleware::class;
        }

        $middleware[] = ResourceAuthorize::class;

        Route::prefix($prefix)
            ->middleware($middleware)
            ->group(function () {
                Route::any('{resource}/{id?}', [
                    GenericApiController::class,
                    'handle',
                ]);
            });
    }
}
