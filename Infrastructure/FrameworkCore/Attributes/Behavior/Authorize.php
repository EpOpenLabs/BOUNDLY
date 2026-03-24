<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Protects an entity's API routes with authentication and role-based access control.
 *
 * The programmer never touches route files or middleware registration.
 * Simply add #[Authorize] to the Entity class.
 *
 * @example
 * ```php
 * // Any authenticated user
 * #[Authorize]
 * class Report extends AggregateRoot { ... }
 *
 * // Only admins
 * #[Authorize(roles: ['admin'])]
 * class Salary extends AggregateRoot { ... }
 *
 * // Public reads, auth writes
 * #[Authorize(roles: [], methods: ['POST', 'PUT', 'DELETE'])]
 * class Article extends AggregateRoot { ... }
 * ```
 *
 * @property array $roles Required role names (empty = any authenticated user)
 * @property array $methods HTTP methods this rule applies to (empty = all)
 * @property string $guard Laravel auth guard (default: sanctum)
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Authorize
{
    public function __construct(
        public array  $roles   = [],
        public array  $methods = [],
        public string $guard   = 'sanctum'
    ) {}
}
