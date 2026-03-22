<?php
namespace Domain\Shared\Events;

interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
