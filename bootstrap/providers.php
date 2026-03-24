<?php

use Infrastructure\FrameworkCore\Providers\FrameworkCoreServiceProvider;
use Infrastructure\Providers\AppServiceProvider;
use Infrastructure\Providers\BroadcastServiceProvider;

return [
    AppServiceProvider::class,
    FrameworkCoreServiceProvider::class,
    BroadcastServiceProvider::class,
];
