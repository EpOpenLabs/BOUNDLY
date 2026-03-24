<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Isolates data by Tenant ID for multi-tenant SaaS applications.
 *
 * Every SELECT, INSERT, UPDATE, and DELETE is automatically scoped to
 * WHERE tenant_id = X-Tenant-ID header value.
 *
 * @example
 * ```php
 * #[Entity(table: 'invoices')]
 * #[TenantAware(tenantColumn: 'tenant_id')]
 * class Invoice extends AggregateRoot { ... }
 * ```
 *
 * Adds column: tenant_id (BIGINT, configurable name)
 *
 * @property string $tenantColumn Column name for tenant identifier
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TenantAware
{
    public function __construct(
        public string $tenantColumn = 'tenant_id'
    ) {}
}
