<?php

namespace Infrastructure\Integrations\WebSockets;

use Domain\Shared\Events\ShouldBroadcastToExterior;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BroadcastableDomainEvent implements ShouldBroadcast
{
    public function __construct(
        public ShouldBroadcastToExterior $domainEvent
    ) {}

    public function broadcastOn(): array
    {
        return [$this->domainEvent->getBroadcastChannel()];
    }

    public function broadcastWith(): array
    {
        return $this->domainEvent->getBroadcastData() ?? [];
    }

    public function broadcastAs(): string
    {
        return 'DomainUpdate';
    }
}
