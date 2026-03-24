<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates IPv4 and/or IPv6 addresses.
 *
 * @example
 * ```php
 * #[IpAddress]
 * private string $ipAddress;
 *
 * #[IpAddress(allowIpv4: true, allowIpv6: false)]
 * private string $ipv4Only;
 * ```
 *
 * @property bool $allowIpv4 Accept IPv4 addresses
 * @property bool $allowIpv6 Accept IPv6 addresses
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IpAddress
{
    public function __construct(
        public bool $allowIpv4 = true,
        public bool $allowIpv6 = true
    ) {}
}
