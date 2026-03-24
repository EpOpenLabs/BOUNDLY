<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonSchema
{
    public function __construct(
        public array $schema,
        public bool $allowAdditionalProperties = false
    ) {}

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function allowsAdditionalProperties(): bool
    {
        return $this->allowAdditionalProperties;
    }
}
