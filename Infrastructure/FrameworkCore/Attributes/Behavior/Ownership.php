<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Ownership
{
    public function __construct(
        public string $ownerField = 'user_id',
        public bool $allowAdminBypass = true,
        public ?string $resourceField = null
    ) {}

    public function getOwnerField(): string
    {
        return $this->ownerField;
    }

    public function allowsAdminBypass(): bool
    {
        return $this->allowAdminBypass;
    }

    public function getResourceField(): ?string
    {
        return $this->resourceField;
    }
}
