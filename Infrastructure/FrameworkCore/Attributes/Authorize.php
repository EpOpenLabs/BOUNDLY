<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

/**
 * Declares authorization requirements for an Entity or Action.
 * When placed on an Entity class, ALL routes for that resource require authentication.
 * The 'roles' parameter restricts access to users with matching role(s).
 * The 'methods' parameter restricts the rule to specific HTTP methods (GET, POST, etc.).
 * Leave 'methods' empty to apply to all methods.
 *
 * Usage:
 *   #[Authorize]                              → Any authenticated user
 *   #[Authorize(roles: ['admin'])]            → Only admins
 *   #[Authorize(roles: ['admin'], methods: ['POST', 'DELETE'])] → Only for write ops
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Authorize
{
    /**
     * @param string[] $roles  Role names required. Empty means "any authenticated user".
     * @param string[] $methods HTTP methods this rule applies to. Empty means all.
     * @param string   $guard  Laravel auth guard to use (e.g. 'sanctum', 'api').
     */
    public function __construct(
        public array  $roles   = [],
        public array  $methods = [],
        public string $guard   = 'sanctum'
    ) {}
}
