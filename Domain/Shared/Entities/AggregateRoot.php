<?php
namespace Domain\Shared\Entities;

use Domain\Shared\Events\DomainEvent;

trait AggregateRoot
{
    private array $domainEvents = [];

    protected function record(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
