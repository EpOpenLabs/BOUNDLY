<?php

namespace Domain\Shared\Events;

/**
 * Interface for events that should be broadcasted to the exterior
 * (e.g. via WebSockets).
 */
interface ShouldBroadcastToExterior
{
    /**
     * Get the name of the channel the event should broadcast on.
     */
    public function getBroadcastChannel(): string;

    /**
     * Get the data that should be broadcasted.
     * If NULL, all public properties of the event will be sent.
     */
    public function getBroadcastData(): ?array;
}
