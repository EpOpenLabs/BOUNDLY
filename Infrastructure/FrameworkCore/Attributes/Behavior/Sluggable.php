<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Automatically generates URL-friendly slugs from another field.
 *
 * Transforms input like "Hello World!" into "hello-world".
 * Handles duplicates by appending numeric suffixes (e.g., category-1).
 *
 * @example
 * ```php
 * #[Sluggable(source: 'name', target: 'slug', unique: true)]
 * #[Column(type: 'string', unique: true)]
 * private string $slug;
 * ```
 *
 * @property string $source Property name to generate slug from
 * @property string $target Property name to store generated slug
 * @property bool $unique Append numeric suffix if duplicate
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Sluggable
{
    public function __construct(
        public string $source,
        public string $target = 'slug',
        public bool $unique = true
    ) {}
}
