<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Maps a Laravel Policy for fine-grained authorization control.
 *
 * Use when complex authorization logic is needed beyond simple role checking.
 * BOUNDLY automatically maps HTTP verbs to policy methods.
 *
 * @example
 * ```php
 * #[Entity(table: 'posts')]
 * #[Policy(PostPolicy::class)]
 * class Post extends AggregateRoot { ... }
 * ```
 *
 * Verb Mapping:
 * - GET /posts -> viewAny
 * - GET /posts/{id} -> view
 * - POST /posts -> create
 * - PUT/PATCH /posts/{id} -> update
 * - DELETE /posts/{id} -> delete
 *
 * @property string|null $class Policy class name
 * @property array $methods Optional verb-to-method mapping
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Policy
{
    public function __construct(
        public ?string $class = null,
        public array $methods = []
    ) {}
}
