<?php

namespace Domain\Comments\Entities;

use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;
use Infrastructure\FrameworkCore\Attributes\MorphTo;
use Domain\Shared\Entities\AggregateRoot;

#[Entity(table: 'comments', resource: 'comments')]
class Comment
{
    use AggregateRoot;

    #[Id]
    private int $id;

    #[Column(type: 'text')]
    private string $content;

    #[MorphTo(name: 'commentable')]
    private $commentable;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getId(): int { return $this->id; }
    public function getContent(): string { return $this->content; }
}
