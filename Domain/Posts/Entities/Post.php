<?php

namespace Domain\Posts\Entities;

use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;
use Infrastructure\FrameworkCore\Attributes\MorphMany;
use Domain\Shared\Entities\AggregateRoot;

#[Entity(table: 'posts', resource: 'posts')]
class Post
{
    use AggregateRoot;

    #[Id]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $title;

    #[MorphMany(relatedEntity: \Domain\Comments\Entities\Comment::class, relation: 'commentable')]
    private array $comments = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
}
