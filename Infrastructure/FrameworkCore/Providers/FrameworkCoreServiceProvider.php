<?php

namespace Infrastructure\FrameworkCore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Registry\ActionRegistry;
use Symfony\Component\Finder\Finder;

class FrameworkCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Merge default configuration
        $this->mergeConfigFrom(__DIR__.'/../Config/boundly.php', 'boundly');

        // 2. Register Singleton Registry for Metadata
        $this->app->singleton(EntityRegistry::class, function () {
            return new EntityRegistry();
        });

        $this->app->singleton(ActionRegistry::class, function () {
            return new ActionRegistry();
        });
    }

    public function boot(): void
    {
        // 3. Set Global Locale from Boundly Configuration
        app()->setLocale(config('boundly.locale', 'en'));

        // 4. Load Translations from the meta-engine resources
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'core');

        // 5. Build Meta-Database (Discovery)
        $this->scanEntities();
        $this->scanActions();

        // 6. Start the Magic API
        $this->registerGenericRoutes();

        // 7. Register Command-Line Interface (BOUNDLY CLI)
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Infrastructure\FrameworkCore\Console\Commands\CoreMigrateCommand::class,
                \Infrastructure\FrameworkCore\Console\Commands\CoreWatchCommand::class,
            ]);

            // Publish config if requested
            $this->publishes([
                __DIR__.'/../Config/boundly.php' => config_path('boundly.php'),
            ], 'boundly-config');
        }
    }

    protected function scanEntities(): void
    {
        $registry = $this->app->make(EntityRegistry::class);
        $srcPath = config('boundly.paths.domain', base_path('Domain'));

        if (is_dir($srcPath)) {
            $finder = new Finder();
            $finder->files()->in($srcPath)->name('*.php');

            foreach ($finder as $file) {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/namespace\s+([^;]+);/', $content, $matchNamespace) && 
                    preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matchClass)) {
                    
                    $fullClassName = $matchNamespace[1] . '\\' . $matchClass[1];
                    $registry->registerClass($fullClassName);
                }
            }
        }
    }

    protected function scanActions(): void
    {
        $registry = $this->app->make(ActionRegistry::class);
        $srcPath = config('boundly.paths.application', base_path('Application'));

        if (is_dir($srcPath)) {
            $finder = new Finder();
            $finder->files()->in($srcPath)->name('*.php');

            foreach ($finder as $file) {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/namespace\s+([^;]+);/', $content, $matchNamespace) && 
                    preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matchClass)) {
                    
                    $fullClassName = $matchNamespace[1] . '\\' . $matchClass[1];
                    $registry->registerClass($fullClassName);
                }
            }
        }
    }

    protected function registerGenericRoutes(): void
    {
        $prefix = config('boundly.api_prefix', 'api');

        Route::prefix($prefix)
             ->middleware(['api']) 
             ->group(function () {
                  Route::any('{resource}/{id?}', [\Infrastructure\FrameworkCore\Http\Controllers\GenericApiController::class, 'handle']);
             });
    }
}
