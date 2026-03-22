<?php

namespace Domain\Posts\Entities;

use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;
use Infrastructure\FrameworkCore\Attributes\BelongsTo;
use Domain\Users\Entities\User;

/**
 * Pure Domain Post Entity.
 */
#[Entity(table: 'posts', resource: 'posts')]
class Post
{
    #[Id]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $title;

    #[Column(type: 'text')]
    private string $content;

    #[BelongsTo(relatedEntity: User::class, nullable: false)]
    private int $user_id;

    public function __construct(string $title, string $content, int $userId)
    {
        $this->title = $title;
        $this->content = $content;
        $this->user_id = $userId;
    }

    public function getTitle(): string { return $this->title; }
    public function getContent(): string { return $this->content; }
    public function getUserId(): int { return $this->user_id; }
}
