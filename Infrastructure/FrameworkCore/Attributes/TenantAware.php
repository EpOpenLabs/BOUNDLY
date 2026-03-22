<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TenantAware
{
    public function __construct(
        public string $tenantColumn = 'tenant_id'
    ) {}
}
