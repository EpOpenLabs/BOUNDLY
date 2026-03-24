<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SecureUpload
{
    public function __construct(
        public array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        public int $maxSize = 10240,
        public array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
        public bool $scanForMalware = false,
        public bool $generateUniqueName = true,
        public ?string $storageDisk = null
    ) {}

    public function getAllowedMimes(): array
    {
        return $this->allowedMimes;
    }

    public function getMaxSizeKb(): int
    {
        return $this->maxSize;
    }

    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    public function shouldScanForMalware(): bool
    {
        return $this->scanForMalware;
    }

    public function shouldGenerateUniqueName(): bool
    {
        return $this->generateUniqueName;
    }

    public function getStorageDisk(): ?string
    {
        return $this->storageDisk;
    }
}
