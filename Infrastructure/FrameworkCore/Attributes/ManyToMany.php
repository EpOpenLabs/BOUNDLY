<?php
namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany
{
    public function __construct(
        public string $relatedEntity,
        public string $pivotTable = '',        // E.g. 'role_user'
        public string $foreignPivotKey = '',   // E.g. 'user_id'
        public string $relatedPivotKey = ''    // E.g. 'role_id'
    ) {}
}
