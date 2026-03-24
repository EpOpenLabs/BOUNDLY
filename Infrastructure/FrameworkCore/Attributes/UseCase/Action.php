<?php

namespace Infrastructure\FrameworkCore\Attributes\UseCase;

use Attribute;

/**
 * Declares a class as an API endpoint (Use Case / Action).
 *
 * Routes are automatically registered based on the resource and method.
 * No routes file needed. BOUNDLY discovers actions during its scan.
 *
 * @example
 * ```php
 * #[Action(resource: 'users/register', method: 'POST')]
 * class RegisterUserAction
 * {
 *     public function execute(Request $request) { ... }
 * }
 * ```
 *
 * @property string $resource The API path (e.g., 'auth/login')
 * @property string $method HTTP method (GET, POST, PUT, DELETE, PATCH)
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Action
{
    public function __construct(
        public string $resource,
        public string $method = 'POST'
    ) {}
}
