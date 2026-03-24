<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Transactional
{
    public function __construct(
        public int $tries = 1,
        public int $timeout = 60,
        public bool $nested = true
    ) {}

    public function getTries(): int
    {
        return $this->tries;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function allowsNested(): bool
    {
        return $this->nested;
    }
}
