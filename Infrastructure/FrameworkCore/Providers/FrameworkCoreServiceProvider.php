<?php

namespace Infrastructure\FrameworkCore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Registry\ActionRegistry;
use Infrastructure\FrameworkCore\Console\Commands\CoreCacheCommand;
use Infrastructure\FrameworkCore\Validation\EntityValidator;
use Symfony\Component\Finder\Finder;

class FrameworkCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Merge default configuration
        $this->mergeConfigFrom(__DIR__ . '/../Config/boundly.php', 'boundly');

        // 2. Register Singleton Registries
        $this->app->singleton(EntityRegistry::class, fn() => new EntityRegistry());
        $this->app->singleton(ActionRegistry::class,  fn() => new ActionRegistry());

        // 3. Register EntityValidator as a singleton
        $this->app->singleton(EntityValidator::class, fn() => new EntityValidator());
    }

    public function boot(): void
    {
        // 4. Set Global Locale
        app()->setLocale(config('boundly.locale', 'en'));

        // 5. Load Translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'core');

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
                \Infrastructure\FrameworkCore\Console\Commands\CoreMigrateCommand::class,
                \Infrastructure\FrameworkCore\Console\Commands\CoreWatchCommand::class,
                \Infrastructure\FrameworkCore\Console\Commands\CoreCacheCommand::class,
                \Infrastructure\FrameworkCore\Console\Commands\CoreDocCommand::class,
                \Infrastructure\FrameworkCore\Console\Commands\CoreMakeTestCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../Config/boundly.php' => config_path('boundly.php'),
            ], 'boundly-config');
        }
    }

    // -------------------------------------------------------------------------
    // Cache Management
    // -------------------------------------------------------------------------

    protected function shouldUseCache(): bool
    {
        $cachePath = CoreCacheCommand::getCachePath();
        return file_exists($cachePath) && !config('boundly.disable_cache', false);
    }

    protected function loadFromCache(): void
    {
        $cachePath = CoreCacheCommand::getCachePath();
        $data      = require $cachePath;

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
        $srcPath  = config('boundly.paths.domain', base_path('Domain'));

        $this->scanDirectory($srcPath, fn(string $class) => $registry->registerClass($class));
    }

    protected function scanActions(): void
    {
        $registry = $this->app->make(ActionRegistry::class);
        $srcPath  = config('boundly.paths.application', base_path('Application'));

        $this->scanDirectory($srcPath, fn(string $class) => $registry->registerClass($class));
    }

    protected function scanDirectory(string $path, callable $callback): void
    {
        if (!is_dir($path)) return;

        $finder = new Finder();
        $finder->files()->in($path)->name('*.php');

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());

            if (preg_match('/namespace\s+([^;]+);/', $content, $ns) &&
                preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $cls)) {
                $callback($ns[1] . '\\' . $cls[1]);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Routes
    // -------------------------------------------------------------------------

    protected function registerGenericRoutes(): void
    {
        $prefix = config('boundly.api_prefix', 'api');

        Route::prefix($prefix)
            ->middleware(['api', \Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class])
            ->group(function () {
                Route::any('{resource}/{id?}', [
                    \Infrastructure\FrameworkCore\Http\Controllers\GenericApiController::class,
                    'handle',
                ]);
            });
    }
}
