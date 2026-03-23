<?php

use Infrastructure\Providers\AppServiceProvider;
use Infrastructure\FrameworkCore\Providers\FrameworkCoreServiceProvider;

return [
    AppServiceProvider::class,
    FrameworkCoreServiceProvider::class,
    \Infrastructure\Providers\BroadcastServiceProvider::class,
];
