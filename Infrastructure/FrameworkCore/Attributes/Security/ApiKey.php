<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Attributes\Security;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ApiKey
{
    public function __construct(
        public string $header = 'X-Api-Key',
        public array $scopes = [],
        public bool $required = true,
        public ?string $description = null
    ) {}
}
