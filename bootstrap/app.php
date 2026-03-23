<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// BOUNDLY: The Clean Root Bootstrap
$app = Application::configure(basePath: realpath(__DIR__.'/../'))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__.'/../Infrastructure/LaravelEngine/routes/web.php',
        commands: __DIR__.'/../Infrastructure/LaravelEngine/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// ⚙️ Hiding the framework organs in the basement
$app->useConfigPath(realpath(__DIR__.'/../Infrastructure/LaravelEngine/config'));
$app->useBootstrapPath(__DIR__);
$app->useStoragePath(realpath(__DIR__.'/../Infrastructure/LaravelEngine/storage'));
$app->useDatabasePath(realpath(__DIR__.'/../Infrastructure/LaravelEngine/database'));

// Resources redirection
$app->instance('path.resources', realpath(__DIR__.'/../Infrastructure/LaravelEngine/resources'));

return $app;
