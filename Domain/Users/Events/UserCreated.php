<?php
namespace Domain\Users\Events;

use Domain\Shared\Events\DomainEvent;
use Domain\Users\Entities\User;

class UserCreated implements DomainEvent
{
    private \DateTimeImmutable $occurredOn;

    public function __construct(public readonly User $user)
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
