<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ThrottleLogin
{
    public function __construct(
        public int $maxAttempts = 5,
        public int $decayMinutes = 15,
        public string $trackBy = 'email',
        public bool $lockoutEnabled = true,
        public int $lockoutMultiplier = 2,
        public int $maxLockouts = 3
    ) {}
}
