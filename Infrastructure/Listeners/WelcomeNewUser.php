<?php

namespace Infrastructure\Listeners;

use Domain\Users\Events\UserCreated;
use Illuminate\Support\Facades\Log;

class WelcomeNewUser
{
    public function handle(UserCreated $event): void
    {
        // Simulamos el envío de un email o una auditoría
        Log::info("DDD EVENT: ¡Bienvenido {$event->user->getName()}! Se ha disparado el Domain Event 'UserCreated' exitosamente.");
    }
}
